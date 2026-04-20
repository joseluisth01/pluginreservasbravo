<?php
/**
 * API Pública simplificada para Partners Externos
 */
class ReservasPublicAPI {

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        $namespace = 'reservas/v1';

        // GET /wp-json/reservas/v1/availability
        register_rest_route($namespace, '/availability', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_availability'),
            'permission_callback' => array($this, 'check_api_key'),
        ));

        // POST /wp-json/reservas/v1/booking
        register_rest_route($namespace, '/booking', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'create_booking'),
            'permission_callback' => array($this, 'check_api_key'),
        ));
    }

    /**
     * Verificar API Key en cada request
     */
    public function check_api_key(WP_REST_Request $request) {
        $api_key    = $request->get_header('X-API-Key');
        $api_secret = $request->get_header('X-API-Secret');

        if (empty($api_key) || empty($api_secret)) {
            return new WP_Error(
                'missing_credentials',
                'Se requieren las cabeceras X-API-Key y X-API-Secret',
                array('status' => 401)
            );
        }

        $partner = $this->get_partner_by_key($api_key, $api_secret);

        if (!$partner) {
            error_log("API: Intento de acceso con credenciales inválidas - Key: $api_key");
            return new WP_Error(
                'invalid_credentials',
                'Credenciales de API inválidas',
                array('status' => 401)
            );
        }

        if ($partner->status !== 'active') {
            return new WP_Error(
                'account_suspended',
                'Esta API key está suspendida',
                array('status' => 403)
            );
        }

        // Guardar partner en el request para usarlo después
        $request->set_param('_partner', $partner);
        $this->log_request($partner->id);

        return true;
    }

    /**
     * GET /availability
     * Parámetros opcionales: date_from, date_to (YYYY-MM-DD)
     * Por defecto devuelve los próximos 30 días
     */
    public function get_availability(WP_REST_Request $request) {
        global $wpdb;

        $date_from = sanitize_text_field($request->get_param('date_from') ?: date('Y-m-d'));
        $date_to   = sanitize_text_field($request->get_param('date_to')   ?: date('Y-m-d', strtotime('+30 days')));

        // Validar formato de fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
            return new WP_Error('invalid_date', 'Formato de fecha inválido. Usa YYYY-MM-DD', array('status' => 400));
        }

        // Máximo 90 días de rango
        if ((strtotime($date_to) - strtotime($date_from)) / 86400 > 90) {
            return new WP_Error('range_too_large', 'El rango máximo es de 90 días', array('status' => 400));
        }

        $table = $wpdb->prefix . 'reservas_servicios';

        $servicios = $wpdb->get_results($wpdb->prepare(
            "SELECT id, fecha, hora, hora_vuelta, plazas_disponibles, plazas_totales,
                    precio_adulto, precio_nino, precio_residente
             FROM $table
             WHERE fecha BETWEEN %s AND %s
               AND fecha >= %s
               AND status = 'active'
               AND enabled = 1
               AND plazas_disponibles > 0
             ORDER BY fecha, hora",
            $date_from,
            $date_to,
            date('Y-m-d')
        ));

        $response = array();
        foreach ($servicios as $s) {
            $response[] = array(
                'service_id'      => (int) $s->id,
                'date'            => $s->fecha,
                'departure_time'  => substr($s->hora, 0, 5),
                'return_time'     => $s->hora_vuelta ? substr($s->hora_vuelta, 0, 5) : null,
                'available_seats' => (int) $s->plazas_disponibles,
                'total_seats'     => (int) $s->plazas_totales,
                'prices'          => array(
                    'adult'    => (float) $s->precio_adulto,
                    'child'    => (float) $s->precio_nino,   // 5-12 años
                    'resident' => (float) $s->precio_residente,
                    'infant'   => 0.00,                        // menores de 5, gratis y no ocupan plaza
                ),
                'currency' => 'EUR',
            );
        }

        $partner = $request->get_param('_partner');
        error_log("API AVAILABILITY: Partner '{$partner->partner_name}' - {$date_from} a {$date_to} - " . count($response) . " servicios");

        return rest_ensure_response(array(
            'success' => true,
            'count'   => count($response),
            'data'    => $response,
        ));
    }

    /**
     * POST /booking
     * 
     * Body JSON esperado:
     * {
     *   "service_id": 42,
     *   "seats": 2,
     *   "partner_booking_id": "CIVITATIS-98765",   <- referencia interna del partner (opcional pero recomendado)
     *   "customer_name": "Ana López"               <- solo para tener algún registro (opcional)
     * }
     */
    public function create_booking(WP_REST_Request $request) {
        global $wpdb;

        $partner = $request->get_param('_partner');
        $body    = $request->get_json_params();

        // Validar campos obligatorios
        if (empty($body['service_id'])) {
            return new WP_Error('missing_service_id', 'service_id es obligatorio', array('status' => 400));
        }

        if (empty($body['seats']) || intval($body['seats']) < 1) {
            return new WP_Error('missing_seats', 'seats es obligatorio y debe ser mayor que 0', array('status' => 400));
        }

        $service_id         = intval($body['service_id']);
        $seats              = intval($body['seats']);
        $partner_booking_id = sanitize_text_field($body['partner_booking_id'] ?? '');
        $customer_name      = sanitize_text_field($body['customer_name'] ?? 'Cliente ' . $partner->partner_name);

        $table_servicios = $wpdb->prefix . 'reservas_servicios';

        // Bloquear la fila para evitar race conditions (dos compras simultáneas)
        $wpdb->query('START TRANSACTION');

        $servicio = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_servicios
             WHERE id = %d AND status = 'active' AND enabled = 1
             FOR UPDATE",
            $service_id
        ));

        if (!$servicio) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('service_not_found', 'Servicio no encontrado o no disponible', array('status' => 404));
        }

        // Verificar que el servicio es futuro
        if (strtotime($servicio->fecha . ' ' . $servicio->hora) <= time()) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('service_in_past', 'Este servicio ya ha pasado', array('status' => 400));
        }

        // Verificar plazas suficientes
        if ($servicio->plazas_disponibles < $seats) {
            $wpdb->query('ROLLBACK');
            return new WP_Error(
                'not_enough_seats',
                "Solo quedan {$servicio->plazas_disponibles} plazas. Solicitadas: {$seats}",
                array('status' => 409)
            );
        }

        // Restar plazas
        $updated = $wpdb->query($wpdb->prepare(
            "UPDATE $table_servicios
             SET plazas_disponibles = plazas_disponibles - %d
             WHERE id = %d AND plazas_disponibles >= %d",
            $seats,
            $service_id,
            $seats
        ));

        if (!$updated) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('update_failed', 'Error actualizando plazas (conflicto de concurrencia)', array('status' => 409));
        }

        // Guardar registro en tabla de reservas API para los reports
        $table_api = $wpdb->prefix . 'reservas_api_bookings';

        $wpdb->insert($table_api, array(
            'partner_id'         => $partner->id,
            'partner_name'       => $partner->partner_name,
            'partner_booking_id' => $partner_booking_id,
            'service_id'         => $service_id,
            'fecha'              => $servicio->fecha,
            'hora'               => $servicio->hora,
            'seats'              => $seats,
            'customer_name'      => $customer_name,
            'status'             => 'confirmed',
            'created_at'         => current_time('mysql'),
        ));

        $api_booking_id = $wpdb->insert_id;

        $wpdb->query('COMMIT');

        error_log("API BOOKING: Partner '{$partner->partner_name}' - Service: $service_id ({$servicio->fecha} {$servicio->hora}) - Seats: $seats - Ref: $partner_booking_id");

        return rest_ensure_response(array(
            'success'            => true,
            'api_booking_id'     => $api_booking_id,
            'partner_booking_id' => $partner_booking_id,
            'service_id'         => $service_id,
            'date'               => $servicio->fecha,
            'departure_time'     => substr($servicio->hora, 0, 5),
            'seats_booked'       => $seats,
            'seats_remaining'    => $servicio->plazas_disponibles - $seats,
            'message'            => 'Plazas reservadas correctamente',
        ));
    }

    // ─── Métodos privados ─────────────────────────────────────────────────────

    private function get_partner_by_key($api_key, $api_secret) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}reservas_api_keys WHERE api_key = %s AND api_secret = %s",
            $api_key,
            $api_secret
        ));
    }

    private function log_request($partner_id) {
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}reservas_api_keys
             SET requests_today = IF(DATE(last_request) = CURDATE(), requests_today + 1, 1),
                 last_request = NOW()
             WHERE id = %d",
            $partner_id
        ));
    }
}