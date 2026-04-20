<?php

/**
 * Clase para gestionar envío de emails del sistema de reservas - CON RECORDATORIOS AUTOMÁTICOS
 * Archivo: wp-content/plugins/sistema-reservas/includes/class-email-service.php
 */
class ReservasEmailService
{
    public function __construct()
    {
        // No se necesitan hooks aquí, será llamado desde otras clases
    }

    /**
     * Enviar solicitud de cancelación al administrador
     */
    public static function send_cancellation_request_to_admin($data)
    {
        try {
            $reserva = $data['reserva'];
            $agency_name = $data['agency_name'];
            $motivo = $data['motivo_cancelacion'];

            // ✅ CORREGIR: Usar la clase correcta para obtener configuración
            $email_admin = ReservasConfigurationAdmin::get_config('email_reservas', get_option('admin_email'));
            $nombre_remitente = ReservasConfigurationAdmin::get_config('nombre_remitente', get_bloginfo('name'));
            $email_remitente = ReservasConfigurationAdmin::get_config('email_remitente', get_option('admin_email'));

            $subject = "Solicitud de Cancelación - Reserva {$reserva['localizador']}";

            $fecha_formateada = date('d/m/Y', strtotime($reserva['fecha']));

            $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);'>
            
            <div style='background: #dc3545; color: white; padding: 20px; text-align: center;'>
                <h1 style='margin: 0; font-size: 24px;'>⚠️ SOLICITUD DE CANCELACIÓN</h1>
            </div>
            
            <div style='padding: 30px;'>
                <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 25px;'>
                    <p style='margin: 0; color: #856404; font-weight: bold;'>
                        La agencia <strong>{$agency_name}</strong> ha solicitado la cancelación de una reserva.
                    </p>
                </div>
                
                <h2 style='color: #dc3545; margin-bottom: 20px;'>Detalles de la Reserva</h2>
                
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 25px;'>
                    <tr>
                        <td style='padding: 8px 0; border-bottom: 1px solid #eee; font-weight: bold; width: 150px;'>Localizador:</td>
                        <td style='padding: 8px 0; border-bottom: 1px solid #eee; color: #0073aa; font-weight: bold;'>{$reserva['localizador']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; border-bottom: 1px solid #eee; font-weight: bold;'>Cliente:</td>
                        <td style='padding: 8px 0; border-bottom: 1px solid #eee;'>{$reserva['nombre']} {$reserva['apellidos']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; border-bottom: 1px solid #eee; font-weight: bold;'>Email:</td>
                        <td style='padding: 8px 0; border-bottom: 1px solid #eee;'>{$reserva['email']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; border-bottom: 1px solid #eee; font-weight: bold;'>Fecha servicio:</td>
                        <td style='padding: 8px 0; border-bottom: 1px solid #eee;'>{$fecha_formateada} a las {$reserva['hora']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; border-bottom: 1px solid #eee; font-weight: bold;'>Personas:</td>
                        <td style='padding: 8px 0; border-bottom: 1px solid #eee;'>{$reserva['total_personas']} personas</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; border-bottom: 1px solid #eee; font-weight: bold;'>Precio:</td>
                       <td style='padding: 8px 0; border-bottom: 1px solid #eee; color: #28a745; font-weight: bold;'>{$reserva['precio_final']}€</td>
                   </tr>
                   <tr>
                       <td style='padding: 8px 0; font-weight: bold;'>Agencia:</td>
                       <td style='padding: 8px 0; color: #7b1fa2; font-weight: bold;'>{$agency_name}</td>
                   </tr>
               </table>
               
               <div style='background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin-bottom: 25px;'>
                   <h3 style='margin: 0 0 10px 0; color: #721c24;'>Motivo de la Solicitud:</h3>
                   <p style='margin: 0; color: #721c24; font-style: italic;'>\"{$motivo}\"</p>
               </div>
               
               <div style='text-align: center; margin-top: 30px;'>
                   <p style='color: #666; margin-bottom: 20px;'>Accede al dashboard para gestionar esta solicitud</p>
                   <a href='" . home_url('/reservas-admin/') . "' style='display: inline-block; background: #0073aa; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Ir al Dashboard</a>
               </div>
           </div>
           
           <div style='background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666;'>
               <p style='margin: 0;'>Este email fue enviado automáticamente por el sistema de reservas</p>
               <p style='margin: 5px 0 0 0;'>Fecha: " . date('d/m/Y H:i') . "</p>
           </div>
       </div>";

            // ✅ CORREGIR: Usar wp_mail directamente en lugar de send_email
            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $nombre_remitente . ' <' . $email_remitente . '>'
            );

            $sent = wp_mail($email_admin, $subject, $body, $headers);

            if ($sent) {
                error_log("✅ Email de cancelación enviado al admin: " . $email_admin);
                return array('success' => true, 'message' => 'Email enviado al administrador correctamente');
            } else {
                error_log("❌ Error enviando email de cancelación al admin: " . $email_admin);
                return array('success' => false, 'message' => 'Error enviando email al administrador');
            }
        } catch (Exception $e) {
            error_log('Error enviando solicitud de cancelación: ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Error enviando email: ' . $e->getMessage()
            );
        }
    }


    /**
     * FUNCIÓN TEMPORAL - Enviar email SIN PDF para testing
     */
    public static function send_customer_confirmation_no_pdf($reserva_data)
    {
        error_log("=== TESTING EMAIL SIN PDF ===");

        $config = self::get_email_config();

        $to = $reserva_data['email'];
        $subject = "TEST - Confirmación de Reserva SIN PDF - Localizador: " . $reserva_data['localizador'];

        $message = self::build_customer_email_template($reserva_data);

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $config['nombre_remitente'] . ' <' . $config['email_remitente'] . '>'
        );

        error_log("=== ENVIANDO EMAIL SIN PDF ===");
        error_log("To: " . $to);
        error_log("From: " . $config['email_remitente']);

        $sent = wp_mail($to, $subject, $message, $headers);

        error_log("Email SIN PDF enviado: " . ($sent ? 'SÍ' : 'NO'));

        if ($sent) {
            error_log("✅ Email SIN PDF enviado correctamente");
            return array('success' => true, 'message' => 'Email sin PDF enviado correctamente');
        } else {
            error_log("❌ Error enviando email sin PDF");
            return array('success' => false, 'message' => 'Error enviando email sin PDF');
        }
    }


public static function send_customer_confirmation($reserva_data)
{
    error_log("=== INICIANDO ENVÍO EMAIL CLIENTE CON PDF ===");
    error_log("Email destino: " . $reserva_data['email']);
    
    $config = self::get_email_config();
    error_log("Configuración email: " . print_r($config, true));

    $to = $reserva_data['email'];
    $subject = "Confirmación de Reserva - Localizador: " . $reserva_data['localizador'];

    $message = self::build_customer_email_template($reserva_data);

    // ✅ MEJORAR CONFIGURACIÓN DE HEADERS PARA WP MAIL SMTP
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $config['nombre_remitente'] . ' <' . $config['email_remitente'] . '>',
        'Reply-To: ' . $config['email_remitente']
    );

    // ✅ AÑADIR PRIORIDAD NORMAL (no alta) para evitar flags de spam
    $headers[] = 'X-Priority: 3'; // Prioridad normal
    $headers[] = 'X-MSMail-Priority: Normal';
    $headers[] = 'Importance: Normal';

    // ✅ GENERAR PDF CON MANEJO DE ERRORES MEJORADO
    $attachments = array();
    $pdf_generated = false;
    
    try {
        error_log('=== INICIANDO GENERACIÓN DE PDF ===');
        
        if (!isset($reserva_data['localizador']) || empty($reserva_data['localizador'])) {
            throw new Exception('Localizador no disponible para generar PDF');
        }
        
        $pdf_path = self::generate_ticket_pdf($reserva_data);
        error_log('PDF generado en: ' . $pdf_path);

        if ($pdf_path && file_exists($pdf_path)) {
            $file_size = filesize($pdf_path);
            error_log("✅ PDF existe - Tamaño: $file_size bytes");

            if ($file_size > 1000) {
                $attachments[] = $pdf_path;
                $pdf_generated = true;
                error_log("✅ PDF añadido a attachments: " . $pdf_path);
            } else {
                error_log("❌ PDF está vacío o muy pequeño: $file_size bytes");
            }
        } else {
            error_log("❌ PDF no existe en: " . ($pdf_path ?? 'path undefined'));
        }
    } catch (Exception $e) {
        error_log("❌ Error generando PDF: " . $e->getMessage());
        error_log("❌ Stack trace: " . $e->getTraceAsString());
    }

    // ✅ NO CONFIGURAR PHPMAILER MANUALMENTE - DEJAR QUE WP MAIL SMTP LO HAGA
    // Eliminar este bloque:
    /* 
    add_action('phpmailer_init', function($phpmailer) use ($config) {
        // ... código anterior ...
    }, 10, 1);
    */

    // ✅ ENVIAR EMAIL CON REINTENTOS Y DELAY
    error_log("=== ENVIANDO EMAIL A CLIENTE ===");
    error_log("To: " . $to);
    error_log("Subject: " . $subject);
    error_log("Attachments: " . ($pdf_generated ? "PDF incluido" : "SIN PDF"));

    $max_attempts = 2; // Reducir a 2 intentos
    $sent = false;
    
    for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
        error_log("Intento #$attempt de envío...");
        
        // ✅ AÑADIR DELAY ENTRE EMAILS PARA EVITAR SPAM FLAGS
        if ($attempt > 1) {
            sleep(3); // Esperar 3 segundos entre reintentos
        }
        
        $sent = wp_mail($to, $subject, $message, $headers, $attachments);
        
        if ($sent) {
            error_log("✅ Email enviado exitosamente en intento #$attempt");
            
            // ✅ DELAY DESPUÉS DE ENVÍO EXITOSO
            usleep(500000); // 0.5 segundos de delay después de envío
            break;
        } else {
            error_log("❌ Fallo en intento #$attempt");
            
            // Obtener error de PHPMailer
            global $phpmailer;
            if (isset($phpmailer) && is_object($phpmailer)) {
                error_log("Error PHPMailer: " . $phpmailer->ErrorInfo);
            }
        }
    }

    // ✅ LIMPIAR ARCHIVOS TEMPORALES
    if (!empty($attachments)) {
        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                wp_schedule_single_event(time() + 300, 'delete_temp_pdf', array($attachment));
                error_log("📅 Programada eliminación de PDF temporal: " . $attachment);
            }
        }
    }

    // ✅ SI FALLÓ, NO REENVIAR ALERTA INMEDIATAMENTE
    if (!$sent) {
        error_log("❌ ERROR CRÍTICO: No se pudo enviar email al cliente después de $max_attempts intentos");
        
        // NO enviar alerta inmediatamente - programar para dentro de 5 minutos
        wp_schedule_single_event(time() + 300, 'send_delayed_email_alert', array($to, $reserva_data['localizador']));
        
        return array(
            'success' => false, 
            'message' => 'Error enviando email al cliente después de múltiples intentos'
        );
    }

    $success_msg = $pdf_generated ? 
        "Email enviado al cliente CON PDF adjunto: " . $to :
        "Email enviado al cliente SIN PDF (error generando PDF): " . $to;
        
    error_log("✅ " . $success_msg);
    
    return array('success' => true, 'message' => $success_msg);
}

public static function send_agency_visita_notification($reserva_data)
{
    error_log('=== ENVIANDO EMAIL A AGENCIA SOBRE VISITA ===');
    
    $config = self::get_email_config();
    
    // ✅ OBTENER EMAIL DE LA AGENCIA
    global $wpdb;
    $table_agencies = $wpdb->prefix . 'reservas_agencies';
    
    $agency = $wpdb->get_row($wpdb->prepare(
        "SELECT email, email_notificaciones, agency_name FROM $table_agencies WHERE id = %d",
        $reserva_data['agency_id']
    ));
    
    if (!$agency) {
        error_log('❌ No se encontró la agencia con ID: ' . $reserva_data['agency_id']);
        return array('success' => false, 'message' => 'Agencia no encontrada');
    }
    
    // ✅ PRIORIZAR email_notificaciones
    $agency_email = !empty($agency->email_notificaciones) ? 
        $agency->email_notificaciones : 
        $agency->email;
    
    if (empty($agency_email)) {
        error_log('❌ La agencia no tiene email configurado');
        return array('success' => false, 'message' => 'Agencia sin email');
    }
    
    error_log('📧 Enviando email a agencia: ' . $agency_email);
    
    $subject = "Nueva Reserva de Visita - " . $reserva_data['localizador'] . " - " . $agency->agency_name;
    
    $message = self::build_agency_visita_notification_template($reserva_data, $agency);
    
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $config['nombre_remitente'] . ' <' . $config['email_remitente'] . '>'
    );
    
    // ✅ ENVIAR CON WP MAIL (usa WP Mail SMTP automáticamente)
    $sent = wp_mail($agency_email, $subject, $message, $headers);
    
    if ($sent) {
        error_log("✅ Email enviado a la agencia: " . $agency_email);
        return array('success' => true, 'message' => 'Email enviado a la agencia');
    } else {
        global $phpmailer;
        $error = isset($phpmailer) ? $phpmailer->ErrorInfo : 'Error desconocido';
        error_log("❌ Error enviando email a la agencia: " . $error);
        return array('success' => false, 'message' => 'Error enviando email a la agencia: ' . $error);
    }
}


private static function build_agency_visita_notification_template($reserva, $agency)
{
    $fecha_formateada = date('d/m/Y', strtotime($reserva['fecha']));
    $fecha_creacion = date('d/m/Y H:i', strtotime($reserva['created_at'] ?? 'now'));

    return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Nueva Reserva de Visita - {$agency->agency_name}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    </style>
</head>
<body style='font-family: \"Inter\", -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; color: #2D2D2D; max-width: 700px; margin: 0 auto; padding: 0; background: #FAFAFA;'>
    
    <!-- Header -->
    <div style='background: linear-gradient(135deg, #0073aa 0%, #005177 100%); color: #FFFFFF; text-align: center; padding: 50px 30px;'>
        <h1 style='margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;'>NUEVA RESERVA DE VISITA</h1>
        <div style='width: 60px; height: 3px; background: #EFCF4B; margin: 20px auto; border-radius: 2px;'></div>
        <p style='margin: 0; font-size: 18px; font-weight: 500; opacity: 0.95;'>{$agency->agency_name}</p>
    </div>

    <!-- Localizador -->
    <div style='background: #EFCF4B; padding: 30px; text-align: center; border-bottom: 1px solid #E0E0E0;'>
        <h2 style='margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: #2D2D2D; text-transform: uppercase; letter-spacing: 1px;'>LOCALIZADOR</h2>
        <div style='font-size: 28px; font-weight: 700; color: #871727; letter-spacing: 3px; font-family: monospace; margin: 10px 0;'>{$reserva['localizador']}</div>
    </div>

    <!-- Información de la reserva -->
    <div style='padding: 40px 30px; border-bottom: 1px solid #E0E0E0;'>
        <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #0073aa; text-align: center;'>Detalles de la Reserva</h3>
        
        <table style='width: 100%; border-collapse: collapse; background: #FFFFFF; border: 2px solid #0073aa; border-radius: 8px; overflow: hidden;'>
            <tr>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Fecha:</td>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #0073aa;'>{$fecha_formateada} - {$reserva['hora']}</td>
            </tr>
            <tr>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Cliente:</td>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; color: #666;'>{$reserva['nombre']} {$reserva['apellidos']}</td>
            </tr>
            <tr>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Email:</td>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; color: #666;'>{$reserva['email']}</td>
            </tr>
            <tr>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Teléfono:</td>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; color: #666;'>{$reserva['telefono']}</td>
            </tr>
            <tr>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Adultos:</td>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #0073aa;'>{$reserva['adultos']}</td>
            </tr>
            <tr>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Niños:</td>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #0073aa;'>{$reserva['ninos']}</td>
            </tr>
            <tr>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Niños menores:</td>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #0073aa;'>{$reserva['ninos_menores']}</td>
            </tr>
            <tr>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Idioma:</td>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #0073aa;'>" . ucfirst($reserva['idioma'] ?? 'español') . "</td>
            </tr>
            <tr style='background: #0073aa;'>
                <td style='padding: 20px 25px; font-size: 20px; font-weight: 700; color: #FFFFFF;'>TOTAL:</td>
                <td style='padding: 20px 25px; text-align: right; font-size: 24px; font-weight: 700; color: #FFFFFF;'>" . number_format($reserva['precio_total'], 2) . "€</td>
            </tr>
        </table>
    </div>

    <!-- Información importante -->
    <div style='padding: 40px 30px; background: #FFFFFF;'>
        <div style='background: #E8F4F8; padding: 30px; border-radius: 8px; border-left: 4px solid #0073aa;'>
            <h4 style='margin: 0 0 15px 0; color: #0073aa;'>✅ Estado:</h4>
            <ul style='margin: 0; padding-left: 20px; color: #2D2D2D;'>
                <li>Reserva confirmada y pagada</li>
                <li>Cliente notificado con billete PDF</li>
                <li>Fecha de reserva: {$fecha_creacion}</li>
            </ul>
        </div>
    </div>

    <!-- Footer -->
    <div style='text-align: center; padding: 40px 30px; background: #2D2D2D; color: #FFFFFF;'>
        <p style='margin: 0; color: #0073aa; font-weight: 600; font-size: 16px;'>
            Sistema de Reservas - Medina Azahara
        </p>
    </div>

</body>
</html>";
}


private static function send_email_failure_alert($cliente_email, $localizador)
{
    error_log("⚠️ Enviando alerta de fallo de email");
    
    $config = self::get_email_config();
    
    $admin_email = ReservasConfigurationAdmin::get_config('email_reservas', get_option('admin_email'));
    
    $subject = "⚠️ ERROR: No se pudo enviar confirmación al cliente - " . $localizador;
    
    $message = "
    <div style='padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107;'>
        <h2 style='color: #856404;'>⚠️ Fallo en Envío de Email</h2>
        <p><strong>Localizador:</strong> {$localizador}</p>
        <p><strong>Email del cliente:</strong> {$cliente_email}</p>
        <p><strong>Fecha/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>
        <p style='margin-top: 20px;'>
            <strong>Acción requerida:</strong> Contacta manualmente con el cliente 
            o reenvía el comprobante desde el panel de administración.
        </p>
        <p style='margin-top: 15px;'>
            <a href='" . home_url('/reservas-admin/') . "' style='display: inline-block; background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir al Dashboard</a>
        </p>
    </div>
    ";
    
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: Sistema de Alertas <' . $config['email_remitente'] . '>'
    );
    
    $sent = wp_mail($admin_email, $subject, $message, $headers);
    
    if ($sent) {
        error_log("📧 Alerta de fallo enviada al admin: " . $admin_email);
    } else {
        error_log("❌ Error enviando alerta de fallo al admin");
    }
}


/**
 * ✅ NUEVA FUNCIÓN: Verificar configuración de correo
 */
public static function test_email_configuration()
{
    error_log('=== PROBANDO CONFIGURACIÓN DE EMAIL ===');
    
    $config = self::get_email_config();
    
    $test_to = $config['email_reservas'];
    $subject = "Prueba de Configuración de Email";
    $message = "Este es un email de prueba para verificar la configuración.";
    
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $config['nombre_remitente'] . ' <' . $config['email_remitente'] . '>'
    );
    
    $sent = wp_mail($test_to, $subject, $message, $headers);
    
    if ($sent) {
        error_log('✅ Email de prueba enviado correctamente');
        return array('success' => true, 'message' => 'Configuración correcta');
    } else {
        global $phpmailer;
        $error = isset($phpmailer) ? $phpmailer->ErrorInfo : 'Error desconocido';
        error_log('❌ Error en email de prueba: ' . $error);
        return array('success' => false, 'message' => $error);
    }
}


    /**
     * ✅ NUEVA FUNCIÓN: Generar PDF del billete
     */
private static function generate_ticket_pdf($reserva_data)
{
    // Cargar la clase del generador de PDF
    if (!class_exists('ReservasPDFGenerator')) {
        require_once RESERVAS_PLUGIN_PATH . 'includes/class-pdf-generator.php';
    }

    try {
        $pdf_generator = new ReservasPDFGenerator();
        $pdf_path = $pdf_generator->generate_ticket_pdf($reserva_data);
        
        // ✅ VALIDACIONES ADICIONALES
        if (!$pdf_path) {
            throw new Exception('PDF generator returned null path');
        }
        
        if (!file_exists($pdf_path)) {
            throw new Exception('PDF file was not created at: ' . $pdf_path);
        }
        
        $file_size = filesize($pdf_path);
        if ($file_size === false || $file_size < 1000) {
            throw new Exception('PDF file is empty or too small: ' . $file_size . ' bytes');
        }
        
        error_log("✅ PDF validado correctamente: {$pdf_path} ({$file_size} bytes)");
        return $pdf_path;
        
    } catch (Exception $e) {
        error_log('❌ Error en generación PDF desde email service: ' . $e->getMessage());
        throw new Exception('Error generando PDF: ' . $e->getMessage());
    }
}

private static function get_admin_email_by_service($reserva_data)
{
    $is_visita = isset($reserva_data['is_visita']) && $reserva_data['is_visita'] === true;
    
    if ($is_visita) {
        return ReservasConfigurationAdmin::get_config('email_visitas', get_option('admin_email'));
    } else {
        return ReservasConfigurationAdmin::get_config('email_reservas', get_option('admin_email'));
    }
}



/**
 * Enviar email INMEDIATO al administrador para visitas guiadas
 */
public static function send_admin_visita_notification_immediate($reserva_data)
{
    error_log('=== ENVIANDO EMAIL INMEDIATO A ADMIN DE VISITAS ===');
    
    $config = self::get_email_config();
    
    // ✅ OBTENER EMAIL DE VISITAS
    $admin_email = ReservasConfigurationAdmin::get_config('email_visitas', get_option('admin_email'));
    
    if (empty($admin_email)) {
        error_log("❌ No hay email_visitas configurado");
        return array('success' => false, 'message' => 'Email de visitas no configurado');
    }
    
    error_log("📧 Enviando email INMEDIATO a: $admin_email");
    error_log("📋 Localizador: " . ($reserva_data['localizador'] ?? 'N/A'));
    
    $subject = "Nueva Reserva de Visita Guiada - " . ($reserva_data['localizador'] ?? 'Sin localizador');
    
    $message = self::build_admin_visita_notification_template($reserva_data);
    
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $config['nombre_remitente'] . ' <' . $config['email_remitente'] . '>',
        'Reply-To: ' . $config['email_remitente'],
        'X-Priority: 3',
        'X-MSMail-Priority: Normal',
        'Importance: Normal'
    );
    
    error_log("📤 Intentando enviar email ahora...");
    
    $max_attempts = 2;
    $sent = false;
    
    for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
        error_log("Intento #$attempt de envío...");
        
        if ($attempt > 1) {
            sleep(2); // Esperar 2 segundos entre reintentos
        }
        
        $sent = wp_mail($admin_email, $subject, $message, $headers);
        
        if ($sent) {
            error_log("✅ Email enviado exitosamente al admin de visitas en intento #$attempt: " . $admin_email);
            usleep(500000); // 0.5 segundos de delay después de envío
            return array('success' => true, 'message' => 'Email enviado al admin de visitas');
        } else {
            error_log("❌ Fallo en intento #$attempt");
            
            global $phpmailer;
            if (isset($phpmailer) && is_object($phpmailer)) {
                error_log("Error PHPMailer: " . $phpmailer->ErrorInfo);
            }
        }
    }
    
    error_log("❌ ERROR CRÍTICO: No se pudo enviar email al admin de visitas después de $max_attempts intentos");
    
    return array('success' => false, 'message' => 'Error enviando email después de múltiples intentos');
}

/**
 * Template de email para admin de visitas
 */
private static function build_admin_visita_notification_template($reserva)
{
    $fecha_formateada = date('d/m/Y', strtotime($reserva['fecha']));
    $fecha_creacion = date('d/m/Y H:i', strtotime($reserva['created_at'] ?? 'now'));
    
    $idioma_display = ucfirst($reserva['idioma'] ?? 'español');

    return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Nueva Reserva de Visita Guiada</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    </style>
</head>
<body style='font-family: \"Inter\", -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; color: #2D2D2D; max-width: 700px; margin: 0 auto; padding: 0; background: #FAFAFA;'>
    
    <!-- Header -->
    <div style='background: linear-gradient(135deg, #0073aa 0%, #005177 100%); color: #FFFFFF; text-align: center; padding: 50px 30px;'>
        <h1 style='margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;'>NUEVA RESERVA DE VISITA GUIADA</h1>
        <div style='width: 60px; height: 3px; background: #EFCF4B; margin: 20px auto; border-radius: 2px;'></div>
        <p style='margin: 0; font-size: 18px; font-weight: 500; opacity: 0.95;'>Se ha recibido una nueva reserva</p>
    </div>

    <!-- Localizador -->
    <div style='background: #EFCF4B; padding: 30px; text-align: center; border-bottom: 1px solid #E0E0E0;'>
        <h2 style='margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: #2D2D2D; text-transform: uppercase; letter-spacing: 1px;'>LOCALIZADOR</h2>
        <div style='font-size: 28px; font-weight: 700; color: #871727; letter-spacing: 3px; font-family: monospace; margin: 10px 0;'>" . $reserva['localizador'] . "</div>
    </div>

    <!-- Información -->
    <div style='padding: 40px 30px; background: #FFFFFF;'>
        <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #0073aa; text-align: center;'>Detalles de la Reserva</h3>
        
        <table style='width: 100%; border-collapse: collapse; background: #FFFFFF; border: 2px solid #0073aa; border-radius: 8px; overflow: hidden;'>
            <tr>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Agencia:</td>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #0073aa;'>" . ($reserva['agency_name'] ?? 'N/A') . "</td>
            </tr>
            <tr>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Fecha:</td>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #0073aa;'>" . $fecha_formateada . " - " . substr($reserva['hora'], 0, 5) . "</td>
            </tr>
            <tr>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Cliente:</td>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; color: #666;'>" . $reserva['nombre'] . " " . $reserva['apellidos'] . "</td>
            </tr>
            <tr>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Email:</td>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; color: #666;'>" . $reserva['email'] . "</td>
            </tr>
            <tr>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Teléfono:</td>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; color: #666;'>" . $reserva['telefono'] . "</td>
            </tr>
            <tr>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Adultos:</td>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #0073aa;'>" . $reserva['adultos'] . "</td>
            </tr>
            <tr>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Niños (5-12):</td>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #0073aa;'>" . $reserva['ninos'] . "</td>
            </tr>
            <tr>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Niños (-5 años):</td>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #0073aa;'>" . $reserva['ninos_menores'] . "</td>
            </tr>
            <tr>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Idioma:</td>
                <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #0073aa;'>$idioma_display</td>
            </tr>
            <tr style='background: #0073aa;'>
                <td style='padding: 20px 25px; font-size: 20px; font-weight: 700; color: #FFFFFF;'>TOTAL:</td>
                <td style='padding: 20px 25px; text-align: right; font-size: 24px; font-weight: 700; color: #FFFFFF;'>" . number_format($reserva['precio_total'], 2) . "€</td>
            </tr>
        </table>
        
        <div style='background: #E8F4F8; padding: 25px; border-radius: 8px; margin-top: 25px; border-left: 4px solid #0073aa;'>
            <h4 style='margin: 0 0 15px 0; color: #0073aa;'>✅ Estado:</h4>
            <ul style='margin: 0; padding-left: 20px; color: #2D2D2D;'>
                <li>Reserva confirmada y pagada</li>
                <li>Cliente notificado con billete PDF</li>
                <li>Agencia notificada</li>
                <li>Fecha de reserva: $fecha_creacion</li>
            </ul>
        </div>
    </div>

    <!-- Footer -->
    <div style='text-align: center; padding: 40px 30px; background: #2D2D2D; color: #FFFFFF;'>
        <p style='margin: 0; color: #0073aa; font-weight: 600; font-size: 16px;'>
            Sistema de Reservas - Visitas Guiadas Medina Azahara
        </p>
    </div>

</body>
</html>";
}


private static function check_email_throttle($email_type = 'general')
{
    $throttle_key = 'reservas_email_throttle_' . $email_type;
    $sent_count = get_transient($throttle_key);
    
    if ($sent_count === false) {
        // Primera vez en esta hora
        set_transient($throttle_key, 1, HOUR_IN_SECONDS);
        return true;
    }
    
    // Límites por tipo de email por hora
    $limits = array(
        'customer' => 50,  // Máximo 50 emails a clientes por hora
        'admin' => 30,     // Máximo 30 emails a admin por hora
        'agency' => 30     // Máximo 30 emails a agencias por hora
    );
    
    $limit = $limits[$email_type] ?? 40;
    
    if ($sent_count >= $limit) {
        error_log("⚠️ THROTTLE: Límite de emails alcanzado para $email_type ($sent_count/$limit)");
        return false;
    }
    
    // Incrementar contador
    set_transient($throttle_key, $sent_count + 1, HOUR_IN_SECONDS);
    return true;
}

/**
 * Enviar email de notificación al administrador (SIN PDF)
 * ✅ ACTUALIZADO: Detecta tipo de servicio y envía al email correcto
 */
public static function send_admin_notification($reserva_data)
{

    if (!self::check_email_throttle('admin')) {
        error_log("⚠️ Email a admin omitido por throttle");
        // Programar para enviar más tarde
        wp_schedule_single_event(time() + 3600, 'send_delayed_admin_notification', array($reserva_data));
        return array('success' => true, 'message' => 'Email programado para envío posterior');
    }

    $config = self::get_email_config();
    $admin_email = self::get_admin_email_by_service($reserva_data);

    if (empty($admin_email)) {
        error_log("❌ No hay email configurado para este tipo de servicio");
        return array('success' => false, 'message' => 'Email de notificaciones no configurado');
    }

    // ✅ EN LUGAR DE ENVIAR INMEDIATAMENTE, AGREGAR A COLA
    $queue_key = 'reservas_admin_notifications_queue';
    $queue = get_transient($queue_key);
    
    if ($queue === false) {
        $queue = array();
    }
    
    $queue[] = array(
        'reserva' => $reserva_data,
        'timestamp' => time()
    );
    
    set_transient($queue_key, $queue, 300); // 5 minutos
    
    // Si es la primera en la cola, programar envío agrupado en 2 minutos
    if (count($queue) === 1) {
        wp_schedule_single_event(time() + 120, 'send_grouped_admin_notifications');
    }
    
    error_log("📋 Notificación agregada a cola de admin (" . count($queue) . " total)");
    
    return array('success' => true, 'message' => 'Notificación agregada a cola');
}


public static function send_grouped_admin_notifications()
{
    $queue_key = 'reservas_admin_notifications_queue';
    $queue = get_transient($queue_key);
    
    if (empty($queue)) {
        return;
    }
    
    error_log("📧 Enviando " . count($queue) . " notificaciones agrupadas a admin");
    
    $config = self::get_email_config();
    $admin_email = ReservasConfigurationAdmin::get_config('email_reservas', get_option('admin_email'));
    
    // Construir email con todas las reservas
    $subject = "Resumen de Reservas - " . count($queue) . " nuevas reservas";
    
    $message = self::build_grouped_admin_email($queue);
    
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $config['nombre_remitente'] . ' <' . $config['email_remitente'] . '>'
    );
    
    $sent = wp_mail($admin_email, $subject, $message, $headers);
    
    if ($sent) {
        delete_transient($queue_key);
        error_log("✅ Notificaciones agrupadas enviadas correctamente");
    } else {
        error_log("❌ Error enviando notificaciones agrupadas");
    }
}


private static function build_grouped_admin_email($queue)
{
    $total_reservas = count($queue);
    $total_personas = 0;
    $total_ingresos = 0;
    
    foreach ($queue as $item) {
        $reserva = $item['reserva'];
        $total_personas += $reserva['total_personas'] ?? 0;
        $total_ingresos += $reserva['precio_final'] ?? 0;
    }
    
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Resumen de Reservas</title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        </style>
    </head>
    <body style='font-family: \"Inter\", -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; color: #2D2D2D; max-width: 700px; margin: 0 auto; padding: 0; background: #FAFAFA;'>
        
        <!-- Header -->
        <div style='background: linear-gradient(135deg, #871727 0%, #A91D33 100%); color: #FFFFFF; text-align: center; padding: 50px 30px;'>
            <h1 style='margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;'>RESUMEN DE RESERVAS</h1>
            <div style='width: 60px; height: 3px; background: #EFCF4B; margin: 20px auto; border-radius: 2px;'></div>
            <p style='margin: 0; font-size: 18px; font-weight: 500; opacity: 0.95;'>$total_reservas nuevas reservas recibidas</p>
        </div>

        <!-- Contenido principal -->
        <div style='background: #FFFFFF; padding: 40px 30px;'>
            
            <!-- Resumen general -->
            <div style='background: #EFCF4B; padding: 30px; text-align: center; border-radius: 10px; margin-bottom: 30px;'>
                <div style='display: flex; justify-content: space-around; flex-wrap: wrap;'>
                    <div style='padding: 10px;'>
                        <div style='font-size: 14px; color: #2D2D2D; margin-bottom: 5px;'>TOTAL RESERVAS</div>
                        <div style='font-size: 32px; font-weight: 700; color: #871727;'>$total_reservas</div>
                    </div>
                    <div style='padding: 10px;'>
                        <div style='font-size: 14px; color: #2D2D2D; margin-bottom: 5px;'>TOTAL PERSONAS</div>
                        <div style='font-size: 32px; font-weight: 700; color: #871727;'>$total_personas</div>
                    </div>
                    <div style='padding: 10px;'>
                        <div style='font-size: 14px; color: #2D2D2D; margin-bottom: 5px;'>INGRESOS TOTALES</div>
                        <div style='font-size: 32px; font-weight: 700; color: #871727;'>" . number_format($total_ingresos, 2) . "€</div>
                    </div>
                </div>
            </div>
            
            <!-- Lista de reservas -->
            <h2 style='color: #871727; margin-bottom: 20px;'>Detalle de Reservas</h2>
    ";
    
    foreach ($queue as $item) {
        $reserva = $item['reserva'];
        $fecha_formateada = date('d/m/Y', strtotime($reserva['fecha']));
        $tiempo_transcurrido = human_time_diff($item['timestamp'], time());
        
        $html .= "
            <div style='background: #F8F9FA; padding: 20px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #EFCF4B;'>
                <div style='display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;'>
                    <div>
                        <div style='font-size: 20px; font-weight: 700; color: #871727; margin-bottom: 5px;'>{$reserva['localizador']}</div>
                        <div style='font-size: 14px; color: #666;'>Hace $tiempo_transcurrido</div>
                    </div>
                    <div style='text-align: right;'>
                        <div style='font-size: 24px; font-weight: 700; color: #28a745;'>" . number_format($reserva['precio_final'], 2) . "€</div>
                    </div>
                </div>
                
                <table style='width: 100%; border-collapse: collapse;'>
                    <tr>
                        <td style='padding: 8px 0; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Cliente:</td>
                        <td style='padding: 8px 0; border-bottom: 1px solid #E0E0E0; text-align: right; color: #666;'>{$reserva['nombre']} {$reserva['apellidos']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Email:</td>
                        <td style='padding: 8px 0; border-bottom: 1px solid #E0E0E0; text-align: right; color: #666;'>{$reserva['email']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Teléfono:</td>
                        <td style='padding: 8px 0; border-bottom: 1px solid #E0E0E0; text-align: right; color: #666;'>{$reserva['telefono']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Fecha servicio:</td>
                        <td style='padding: 8px 0; border-bottom: 1px solid #E0E0E0; text-align: right; color: #666;'>$fecha_formateada a las " . substr($reserva['hora'], 0, 5) . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; font-weight: 600; color: #2D2D2D;'>Personas:</td>
                        <td style='padding: 8px 0; text-align: right; font-weight: 700; color: #871727;'>{$reserva['total_personas']} personas</td>
                    </tr>
                </table>
            </div>
        ";
    }
    
    $html .= "
            <!-- Acciones -->
            <div style='text-align: center; margin-top: 40px; padding: 30px; background: #871727; border-radius: 8px;'>
                <p style='margin: 0 0 20px 0; color: #FFFFFF; font-size: 18px; font-weight: 600;'>
                    Accede al dashboard para gestionar estas reservas
                </p>
                <a href='" . home_url('/reservas-admin/') . "' style='display: inline-block; background: #EFCF4B; color: #2E2D2C; padding: 15px 40px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;'>IR AL DASHBOARD</a>
            </div>
        </div>

        <!-- Footer -->
        <div style='text-align: center; padding: 40px 30px; background: #2D2D2D; color: #FFFFFF;'>
            <div style='width: 40px; height: 2px; background: #EFCF4B; margin: 0 auto 20px;'></div>
            <p style='margin: 0 0 15px 0; font-size: 14px; opacity: 0.8; line-height: 1.6;'>
                Este es un resumen automático de reservas.<br>
                Puedes gestionar todas las reservas desde el panel de administración.
            </p>
            <p style='margin: 0; color: #EFCF4B; font-weight: 600; font-size: 16px;'>
                Sistema de Reservas - Medina Azahara
            </p>
        </div>

    </body>
    </html>";
    
    return $html;
}

public static function send_admin_notification_delayed($reserva_data)
{
    error_log('📧 Enviando notificación de admin retrasada');
    
    $config = self::get_email_config();
    $admin_email = self::get_admin_email_by_service($reserva_data);

    if (empty($admin_email)) {
        error_log("❌ No hay email configurado para este tipo de servicio");
        return array('success' => false, 'message' => 'Email de notificaciones no configurado');
    }

    $is_visita = isset($reserva_data['is_visita']) && $reserva_data['is_visita'] === true;
    $tipo_servicio = $is_visita ? 'Visita Guiada' : 'Autobús';

    $to = $admin_email;
    $subject = "Nueva Reserva Recibida ({$tipo_servicio}) - " . $reserva_data['localizador'];

    $message = self::build_admin_email_template($reserva_data);

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $config['nombre_remitente'] . ' <' . $config['email_remitente'] . '>'
    );

    $sent = wp_mail($to, $subject, $message, $headers);

    if ($sent) {
        error_log("✅ Email retrasado enviado al administrador ({$tipo_servicio}): " . $to);
        return array('success' => true, 'message' => 'Email enviado al administrador correctamente');
    } else {
        error_log("❌ Error enviando email retrasado al administrador ({$tipo_servicio}): " . $to);
        return array('success' => false, 'message' => 'Error enviando email al administrador');
    }
}




public static function send_reminder_email($reserva_data)
{
    $config = self::get_email_config();

    $to = $reserva_data['email'];
    $fecha_servicio = date('d/m/Y', strtotime($reserva_data['fecha']));
    $subject = "Recordatorio - Tu viaje es mañana - Localizador: " . $reserva_data['localizador'];

    $message = self::build_reminder_email_template($reserva_data);

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $config['nombre_remitente'] . ' <' . $config['email_remitente'] . '>'
    );

    // ✅ ADJUNTAR PDF TAMBIÉN EN RECORDATORIOS CON MANEJO DE ERRORES
    $attachments = array();
    try {
        $pdf_path = self::generate_ticket_pdf($reserva_data);
        if ($pdf_path && file_exists($pdf_path) && filesize($pdf_path) > 1000) {
            $attachments[] = $pdf_path;
            error_log("✅ PDF generado para recordatorio: " . $pdf_path);
        }
    } catch (Exception $e) {
        error_log("❌ Error generando PDF para recordatorio: " . $e->getMessage());
        // Continuar sin PDF
    }

    $sent = wp_mail($to, $subject, $message, $headers, $attachments);

    // Limpiar archivo temporal
    if (!empty($attachments)) {
        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                wp_schedule_single_event(time() + 300, 'delete_temp_pdf', array($attachment));
            }
        }
    }

    if ($sent) {
        error_log("✅ Email de recordatorio enviado al cliente: " . $to);
        return array('success' => true, 'message' => 'Recordatorio enviado correctamente');
    } else {
        error_log("❌ Error enviando recordatorio al cliente: " . $to);
        return array('success' => false, 'message' => 'Error enviando recordatorio');
    }
}

private static function get_email_config()
{
    if (!class_exists('ReservasConfigurationAdmin')) {
        require_once RESERVAS_PLUGIN_PATH . 'includes/class-configuration-admin.php';
    }

    return array(
        'email_remitente' => ReservasConfigurationAdmin::get_config('email_remitente', get_option('admin_email')),
        'nombre_remitente' => ReservasConfigurationAdmin::get_config('nombre_remitente', get_bloginfo('name')),
        'email_reservas' => ReservasConfigurationAdmin::get_config('email_reservas', get_option('admin_email')),
        'email_visitas' => ReservasConfigurationAdmin::get_config('email_visitas', get_option('admin_email')), // ✅ YA EXISTE
    );
}

private static function build_customer_email_template($reserva)
{
    $fecha_formateada = date('d/m/Y', strtotime($reserva['fecha']));
    $fecha_creacion = date('d/m/Y H:i', strtotime($reserva['created_at'] ?? 'now'));

    // ✅ DETECTAR SI ES VISITA GUIADA
    $is_visita = isset($reserva['is_visita']) && $reserva['is_visita'] === true;

    $personas_detalle = "";
    if ($reserva['adultos'] > 0) $personas_detalle .= "Adultos: " . $reserva['adultos'] . "<br>";
    if ($reserva['residentes'] > 0) $personas_detalle .= "Residentes: " . $reserva['residentes'] . "<br>";
    if ($reserva['ninos_5_12'] > 0) $personas_detalle .= "Niños (5-12 años): " . $reserva['ninos_5_12'] . "<br>";
    if ($reserva['ninos_menores'] > 0) $personas_detalle .= "Niños menores (gratis): " . $reserva['ninos_menores'] . "<br>";

    $descuento_info = "";
    if ($reserva['descuento_total'] > 0) {
        $descuento_info = "<tr>
        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; background: #FFF8DC; font-weight: 600; color: #871727;'>Descuentos aplicados:</td>
        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; background: #FFF8DC; text-align: right; color: #871727; font-weight: bold; font-size: 16px;'>-" . number_format($reserva['descuento_total'], 2) . "€</td>
    </tr>";
    }

    // ✅ DEFINIR NOMBRE DEL PRODUCTO SEGÚN TIPO
    if ($is_visita) {
        $producto_nombre = 'Visita Guiada Medina Azahara';
        $producto_detalle = 'Visita Guiada Medina Azahara (' . substr($reserva['hora'], 0, 5) . ' hrs)';
    } else {
        $producto_nombre = 'TAQ BUS Madinat Al-Zahra + Lanzadera';
        $producto_detalle = 'TAQ BUS Madinat Al-Zahra + Lanzadera (' . substr($reserva['hora'], 0, 5) . ' hrs)';
    }

    return "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Confirmación de Reserva - Medina Azahara</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    </style>
</head>
<body style='font-family: \"Inter\", -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; color: #2D2D2D; max-width: 600px; margin: 0 auto; padding: 0; background: #FAFAFA;'>
    
    <!-- Header -->
    <div style='background: linear-gradient(135deg, #871727 0%, #A91D33 100%); color: #FFFFFF; text-align: center; padding: 50px 30px;'>
        <h1 style='margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;'>RESERVA CONFIRMADA</h1>
        <div style='width: 60px; height: 3px; background: #EFCF4B; margin: 20px auto; border-radius: 2px;'></div>
        <p style='margin: 0; font-size: 18px; font-weight: 500; opacity: 0.95;'>Tu " . ($is_visita ? 'visita guiada' : 'viaje') . " a Medina Azahara está asegurado</p>
    </div>

    <!-- Contenido principal -->
    <div style='background: #FFFFFF; padding: 0;'>
        
        <!-- Localizador destacado -->
        <div style='background: #EFCF4B; padding: 30px; text-align: center; border-bottom: 1px solid #E0E0E0;'>
            <h2 style='margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: #2D2D2D; text-transform: uppercase; letter-spacing: 1px;'>LOCALIZADOR DE RESERVA</h2>
            <div style='font-size: 28px; font-weight: 700; color: #871727; letter-spacing: 3px; font-family: monospace; margin: 10px 0;'>" . $reserva['localizador'] . "</div>
            <p style='margin: 0; font-size: 14px; color: #2D2D2D; font-weight: 500;'>Presenta este código al " . ($is_visita ? 'iniciar la visita' : 'subir al autobús') . "</p>
        </div>

        <!-- Información de la reserva -->
        <div style='padding: 40px 30px; border-bottom: 1px solid #E0E0E0;'>
            <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Detalles de tu Reserva</h3>
            
            <table style='width: 100%; border-collapse: collapse; background: #FFFFFF; border: 2px solid #EFCF4B; border-radius: 8px; overflow: hidden;'>
                <tr>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Fecha " . ($is_visita ? 'de la visita' : 'del viaje') . ":</td>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #871727;'>" . $fecha_formateada . "</td>
                </tr>
                <tr>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Hora de " . ($is_visita ? 'inicio' : 'salida') . ":</td>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #871727; font-size: 18px;'>" . substr($reserva['hora'], 0, 5) . "</td>
                </tr>";
    
    // ✅ HORA DE VUELTA - SOLO PARA AUTOBÚS
    if (!$is_visita) {
        $return_html = "
                <tr>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Hora de vuelta:</td>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #871727;'>" . substr($reserva['hora_vuelta'] ?? '', 0, 5) . "</td>
                </tr>";
    } else {
        $return_html = "";
    }
    
    $html_content = $return_html . "
                <tr>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Fecha de reserva:</td>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; color: #666666;'>" . $fecha_creacion . "</td>
                </tr>
            </table>
        </div>

        <!-- Datos del cliente -->
        <div style='padding: 40px 30px; background: #F8F9FA; border-bottom: 1px solid #E0E0E0;'>
            <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Datos del Viajero</h3>
            
            <div style='background: #FFFFFF; padding: 25px; border-radius: 8px; border: 1px solid #E0E0E0;'>
                <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #871727;'>Nombre completo:</strong> " . $reserva['nombre'] . " " . $reserva['apellidos'] . "</p>
                <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #871727;'>Email:</strong> " . $reserva['email'] . "</p>
                <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #871727;'>Teléfono:</strong> " . $reserva['telefono'] . "</p>
            </div>
        </div>

        <!-- Distribución de personas -->
        <div style='padding: 40px 30px; border-bottom: 1px solid #E0E0E0;'>
            <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Distribución de Viajeros</h3>
            
            <div style='background: #F8F9FA; padding: 25px; border-radius: 8px; border: 1px solid #E0E0E0;'>
                <div style='font-size: 16px; color: #2D2D2D; line-height: 1.8;'>
                    " . $personas_detalle . "
                </div>
                <div style='margin-top: 20px; padding-top: 20px; border-top: 2px solid #EFCF4B; text-align: center;'>
                    <p style='margin: 0; font-weight: 700; color: #871727; font-size: 18px;'>Total personas con plaza: " . $reserva['total_personas'] . "</p>
                </div>
            </div>
        </div>

        <!-- Resumen de precios -->
        <div style='padding: 40px 30px; background: #F8F9FA;'>
            <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Resumen de Precios</h3>
            
            <table style='width: 100%; border-collapse: collapse; background: #FFFFFF; border: 2px solid #EFCF4B; border-radius: 8px; overflow: hidden;'>
                <tr>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Precio base:</td>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 600; color: #2D2D2D;'>" . number_format($reserva['precio_base'], 2) . "€</td>
                </tr>
                " . $descuento_info . "
                <tr style='background: #871727;'>
                    <td style='padding: 20px 25px; font-size: 20px; font-weight: 700; color: #FFFFFF;'>TOTAL PAGADO:</td>
                    <td style='padding: 20px 25px; text-align: right; font-size: 24px; font-weight: 700; color: #FFFFFF;'>" . number_format($reserva['precio_final'], 2) . "€</td>
                </tr>
            </table>
        </div>

        <!-- Información importante -->
        <div style='padding: 40px 30px; background: #FFFFFF;'>
            <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Información Importante</h3>
            
            <div style='background: #F8F9FA; padding: 30px; border-radius: 8px; border-left: 4px solid #EFCF4B;'>
                <ul style='margin: 0; padding-left: 25px; color: #2D2D2D; line-height: 1.8; font-size: 16px;'>
                    <li style='margin: 12px 0;'><strong style='color: #871727;'>Presenta tu localizador:</strong> <span style='background: #EFCF4B; color: #2D2D2D; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-family: monospace;'>" . $reserva['localizador'] . "</span> al " . ($is_visita ? 'iniciar la visita' : 'subir al autobús') . "</li>
                    <li style='margin: 12px 0;'><strong style='color: #871727;'>Puntualidad:</strong> Preséntate 15 minutos antes de la hora de " . ($is_visita ? 'inicio' : 'salida') . "</li>";
    
    // ✅ INFORMACIÓN ESPECÍFICA SEGÚN TIPO
    if (!$is_visita) {
        $html_content .= "
                    <li style='margin: 12px 0;'><strong style='color: #871727;'>Residentes:</strong> Deben presentar documento acreditativo de residencia en Córdoba</li>
                    <li style='margin: 12px 0;'><strong style='color: #871727;'>Niños menores:</strong> Los menores de 5 años viajan gratis sin ocupar plaza</li>";
    } else {
        $html_content .= "
                    <li style='margin: 12px 0;'><strong style='color: #871727;'>Duración:</strong> Aproximadamente 3 horas y media</li>
                    <li style='margin: 12px 0;'><strong style='color: #871727;'>Niños menores:</strong> Los menores de 5 años no pagan entrada</li>";
    }
    
    $html_content .= "
                    <li style='margin: 12px 0;'><strong style='color: #871727;'>Contacto:</strong> Para cualquier consulta, contacta con nosotros</li>
                </ul>
            </div>
            
            <!-- Mensaje final -->
            <div style='text-align: center; margin-top: 40px; padding: 30px; background: #871727; border-radius: 8px;'>
                <p style='margin: 0; color: #FFFFFF; font-size: 20px; font-weight: 700;'>
                    ¡Disfruta de tu " . ($is_visita ? 'visita guiada a' : 'viaje a') . " Medina Azahara!
                </p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div style='text-align: center; padding: 40px 30px; background: #2D2D2D; color: #FFFFFF;'>
        <div style='width: 40px; height: 2px; background: #EFCF4B; margin: 0 auto 20px;'></div>
        <p style='margin: 0 0 15px 0; font-size: 14px; opacity: 0.8; line-height: 1.6;'>
            Este es un email automático de confirmación de tu reserva.<br>
            Si tienes alguna duda, ponte en contacto con nosotros.
        </p>
        <p style='margin: 0; color: #EFCF4B; font-weight: 600; font-size: 16px;'>
            Gracias por elegir nuestros servicios
        </p>
    </div>

</body>
</html>";

    return $html_content;
}

    private static function build_reminder_email_template($reserva)
    {
        $fecha_formateada = date('d/m/Y', strtotime($reserva['fecha']));
        $dia_semana = date('l', strtotime($reserva['fecha']));
        $dias_semana_es = array(
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        );
        $dia_semana_es = $dias_semana_es[$dia_semana] ?? $dia_semana;

        $personas_detalle = "";
        if ($reserva['adultos'] > 0) $personas_detalle .= "Adultos: " . $reserva['adultos'] . "<br>";
        if ($reserva['residentes'] > 0) $personas_detalle .= "Residentes: " . $reserva['residentes'] . "<br>";
        if ($reserva['ninos_5_12'] > 0) $personas_detalle .= "Niños (5-12 años): " . $reserva['ninos_5_12'] . "<br>";
        if ($reserva['ninos_menores'] > 0) $personas_detalle .= "Niños menores (gratis): " . $reserva['ninos_menores'] . "<br>";

        return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Recordatorio de Viaje - Medina Azahara</title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        </style>
    </head>
    <body style='font-family: \"Inter\", -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; color: #2D2D2D; max-width: 600px; margin: 0 auto; padding: 0; background: #FAFAFA;'>
        
        <!-- Header -->
        <div style='background: linear-gradient(135deg, #871727 0%, #A91D33 100%); color: #FFFFFF; text-align: center; padding: 50px 30px;'>
            <h1 style='margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;'>RECORDATORIO DE VIAJE</h1>
            <div style='width: 60px; height: 3px; background: #EFCF4B; margin: 20px auto; border-radius: 2px;'></div>
            <p style='margin: 0; font-size: 18px; font-weight: 500; opacity: 0.95;'>Tu visita a Medina Azahara es muy pronto</p>
        </div>

        <!-- Contenido principal -->
        <div style='background: #FFFFFF; padding: 0;'>
            
            <!-- Localizador destacado -->
            <div style='background: #EFCF4B; padding: 30px; text-align: center; border-bottom: 1px solid #E0E0E0;'>
                <h2 style='margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: #2D2D2D; text-transform: uppercase; letter-spacing: 1px;'>LOCALIZADOR DE RESERVA</h2>
                <div style='font-size: 28px; font-weight: 700; color: #871727; letter-spacing: 3px; font-family: monospace; margin: 10px 0;'>" . $reserva['localizador'] . "</div>
                <p style='margin: 0; font-size: 14px; color: #2D2D2D; font-weight: 500;'>Tu viaje es mañana</p>
            </div>

            <!-- Información del viaje -->
            <div style='padding: 40px 30px; border-bottom: 1px solid #E0E0E0;'>
                <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Detalles de tu Viaje</h3>
                
                <table style='width: 100%; border-collapse: collapse; background: #FFFFFF; border: 2px solid #EFCF4B; border-radius: 8px; overflow: hidden;'>
                    <tr>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Fecha:</td>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #871727;'>" . $dia_semana_es . ", " . $fecha_formateada . "</td>
                    </tr>
                    <tr>
    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Hora de salida:</td>
    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #871727;'>" . substr($reserva['hora'], 0, 5) . "</td>
</tr>
<tr>
    <td style='padding: 15px 25px; font-weight: 600; color: #2D2D2D;'>Hora de vuelta:</td>
    <td style='padding: 15px 25px; text-align: right; font-weight: 700; color: #871727;'>" . substr($reserva['hora_vuelta'] ?? '', 0, 5) . "</td>
</tr>
                    <tr>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Cliente:</td>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; color: #666666;'>" . $reserva['nombre'] . " " . $reserva['apellidos'] . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 15px 25px; font-weight: 600; color: #2D2D2D;'>Teléfono:</td>
                        <td style='padding: 15px 25px; text-align: right; color: #666666;'>" . $reserva['telefono'] . "</td>
                    </tr>
                </table>
            </div>

            <!-- Distribución de personas -->
            <div style='padding: 40px 30px; background: #F8F9FA; border-bottom: 1px solid #E0E0E0;'>
                <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Personas en tu Reserva</h3>
                
                <div style='background: #FFFFFF; padding: 25px; border-radius: 8px; border: 1px solid #E0E0E0;'>
                    <div style='font-size: 16px; color: #2D2D2D; line-height: 1.8;'>
                        " . $personas_detalle . "
                    </div>
                    <div style='margin-top: 20px; padding-top: 20px; border-top: 2px solid #EFCF4B; text-align: center;'>
                        <p style='margin: 0; font-weight: 700; color: #871727; font-size: 18px;'>Total personas con plaza: " . $reserva['total_personas'] . "</p>
                    </div>
                </div>
            </div>

            <!-- Recordatorios importantes -->
            <div style='padding: 40px 30px; background: #FFFFFF; border-bottom: 1px solid #E0E0E0;'>
                <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Recordatorios Importantes</h3>
                
                <div style='background: #F8F9FA; padding: 30px; border-radius: 8px; border-left: 4px solid #EFCF4B;'>
                    <ul style='margin: 0; padding-left: 25px; color: #2D2D2D; line-height: 1.8; font-size: 16px;'>
                        <li style='margin: 12px 0;'><strong style='color: #871727;'>Presenta tu localizador:</strong> <span style='background: #EFCF4B; color: #2D2D2D; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-family: monospace;'>" . $reserva['localizador'] . "</span> al subir al autobús</li>
                        <li style='margin: 12px 0;'><strong style='color: #871727;'>Puntualidad:</strong> Llega 15 minutos antes de las " . substr($reserva['hora'], 0, 5) . "</li>
                        <li style='margin: 12px 0;'><strong style='color: #871727;'>Residentes:</strong> Deben presentar documento acreditativo de residencia en Córdoba</li>
                        <li style='margin: 12px 0;'><strong style='color: #871727;'>Niños menores:</strong> Los menores de 5 años viajan gratis sin ocupar plaza</li>
                        <li style='margin: 12px 0;'><strong style='color: #871727;'>Punto de encuentro:</strong> Paseo de la Victoria (glorieta Hospital Cruz Roja)</li>
                    </ul>
                </div>
            </div>

            <!-- Total de la reserva -->
            <div style='padding: 40px 30px; background: #F8F9FA;'>
                <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Total de tu Reserva</h3>
                
                <table style='width: 100%; border-collapse: collapse; background: #FFFFFF; border: 2px solid #EFCF4B; border-radius: 8px; overflow: hidden;'>
                    <tr style='background: #871727;'>
                        <td style='padding: 20px 25px; font-size: 20px; font-weight: 700; color: #FFFFFF;'>TOTAL PAGADO:</td>
                        <td style='padding: 20px 25px; text-align: right; font-size: 24px; font-weight: 700; color: #FFFFFF;'>" . number_format($reserva['precio_final'], 2) . "€</td>
                    </tr>
                </table>
                
                <div style='text-align: center; margin-top: 30px; padding: 25px; background: #FFFFFF; border: 1px solid #E0E0E0; border-radius: 8px;'>
                    <p style='margin: 0; color: #28a745; font-weight: 700; font-size: 16px;'>Reserva confirmada y pagada</p>
                </div>
            </div>

            <!-- Mensaje final -->
            <div style='padding: 40px 30px; background: #FFFFFF;'>
                <div style='text-align: center; padding: 30px; background: #871727; border-radius: 8px;'>
                    <h3 style='color: #FFFFFF; margin: 0 0 15px 0; font-size: 24px; font-weight: 700;'>¿Todo preparado?</h3>
                    <p style='margin: 0 0 15px 0; color: #FFFFFF; font-size: 18px; font-weight: 500;'>
                        Te esperamos mañana para descubrir juntos las maravillas de Medina Azahara
                    </p>
                    <p style='margin: 0; color: #EFCF4B; font-size: 16px; font-weight: 600;'>
                        Si tienes alguna duda de última hora, no dudes en contactarnos
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style='text-align: center; padding: 40px 30px; background: #2D2D2D; color: #FFFFFF;'>
            <div style='width: 40px; height: 2px; background: #EFCF4B; margin: 0 auto 20px;'></div>
            <p style='margin: 0 0 15px 0; font-size: 14px; opacity: 0.8; line-height: 1.6;'>
                Este es un recordatorio automático de tu reserva para mañana.<br>
                ¡Te deseamos un viaje fantástico!
            </p>
            <p style='margin: 0; color: #EFCF4B; font-weight: 600; font-size: 16px;'>
                Medina Azahara te espera
            </p>
        </div>

    </body>
    </html>";
    }

    /**
     * Template de email para el administrador
     */
    private static function build_admin_email_template($reserva)
    {
        $fecha_formateada = date('d/m/Y', strtotime($reserva['fecha']));
        $fecha_creacion = date('d/m/Y H:i', strtotime($reserva['created_at'] ?? 'now'));

        $personas_detalle = "";
        if ($reserva['adultos'] > 0) $personas_detalle .= "Adultos: " . $reserva['adultos'] . "<br>";
        if ($reserva['residentes'] > 0) $personas_detalle .= "Residentes: " . $reserva['residentes'] . "<br>";
        if ($reserva['ninos_5_12'] > 0) $personas_detalle .= "Niños (5-12 años): " . $reserva['ninos_5_12'] . "<br>";
        if ($reserva['ninos_menores'] > 0) $personas_detalle .= "Niños menores (gratis): " . $reserva['ninos_menores'] . "<br>";

        $descuento_info = "";
        if ($reserva['descuento_total'] > 0) {
            $descuento_info = "<tr>
            <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; background: #FFF8DC; font-weight: 600; color: #871727;'>Descuentos aplicados:</td>
            <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; background: #FFF8DC; text-align: right; color: #871727; font-weight: bold; font-size: 16px;'>-" . number_format($reserva['descuento_total'], 2) . "€</td>
        </tr>";
        }

        return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Nueva Reserva Recibida - Sistema Medina Azahara</title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        </style>
    </head>
    <body style='font-family: \"Inter\", -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; color: #2D2D2D; max-width: 700px; margin: 0 auto; padding: 0; background: #FAFAFA;'>
        
        <!-- Header Administrativo -->
        <div style='background: linear-gradient(135deg, #871727 0%, #A91D33 100%); color: #FFFFFF; text-align: center; padding: 50px 30px;'>
            <h1 style='margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;'>NUEVA RESERVA RECIBIDA</h1>
            <div style='width: 60px; height: 3px; background: #EFCF4B; margin: 20px auto; border-radius: 2px;'></div>
            <p style='margin: 0; font-size: 18px; font-weight: 500; opacity: 0.95;'>Se ha procesado una nueva reserva en el sistema</p>
        </div>

        <!-- Contenido principal -->
        <div style='background: #FFFFFF; padding: 0;'>
            
            <!-- Localizador destacado -->
            <div style='background: #EFCF4B; padding: 30px; text-align: center; border-bottom: 1px solid #E0E0E0;'>
                <h2 style='margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: #2D2D2D; text-transform: uppercase; letter-spacing: 1px;'>LOCALIZADOR DE RESERVA</h2>
                <div style='font-size: 28px; font-weight: 700; color: #871727; letter-spacing: 3px; font-family: monospace; margin: 10px 0;'>" . $reserva['localizador'] . "</div>
                <p style='margin: 0; font-size: 14px; color: #2D2D2D; font-weight: 500;'>Nueva reserva para revisar</p>
            </div>

            <!-- Información de la reserva -->
            <div style='padding: 40px 30px; border-bottom: 1px solid #E0E0E0;'>
                <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Información de la Reserva</h3>
                
                <table style='width: 100%; border-collapse: collapse; background: #FFFFFF; border: 2px solid #EFCF4B; border-radius: 8px; overflow: hidden;'>
                    <tr>
    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Fecha del servicio:</td>
    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #871727;'>" . $fecha_formateada . " - Salida: " . substr($reserva['hora'], 0, 5) . " - Vuelta: " . substr($reserva['hora_vuelta'] ?? '', 0, 5) . "</td>
</tr>
                    <tr>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Fecha de reserva:</td>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; color: #666666;'>" . $fecha_creacion . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Total personas:</td>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #871727; font-size: 18px;'>" . $reserva['total_personas'] . " plazas ocupadas</td>
                    </tr>
                    <tr>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Precio base:</td>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 600; color: #2D2D2D;'>" . number_format($reserva['precio_base'], 2) . "€</td>
                    </tr>
                    " . $descuento_info . "
                    <tr style='background: #871727;'>
                        <td style='padding: 20px 25px; font-size: 20px; font-weight: 700; color: #FFFFFF;'>TOTAL PAGADO:</td>
                        <td style='padding: 20px 25px; text-align: right; font-size: 24px; font-weight: 700; color: #FFFFFF;'>" . number_format($reserva['precio_final'], 2) . "€</td>
                    </tr>
                </table>
            </div>

            <!-- Datos del cliente -->
            <div style='padding: 40px 30px; background: #F8F9FA; border-bottom: 1px solid #E0E0E0;'>
                <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Datos del Cliente</h3>
                
                <div style='background: #FFFFFF; padding: 25px; border-radius: 8px; border: 1px solid #E0E0E0;'>
                    <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #871727;'>Nombre completo:</strong> " . $reserva['nombre'] . " " . $reserva['apellidos'] . "</p>
                    <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #871727;'>Email:</strong> <a href='mailto:" . $reserva['email'] . "' style='color: #871727; text-decoration: none;'>" . $reserva['email'] . "</a></p>
                    <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #871727;'>Teléfono:</strong> <a href='tel:" . $reserva['telefono'] . "' style='color: #871727; text-decoration: none;'>" . $reserva['telefono'] . "</a></p>
                </div>
            </div>

            <!-- Distribución de personas -->
            <div style='padding: 40px 30px; border-bottom: 1px solid #E0E0E0;'>
                <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Distribución de Viajeros</h3>
                
                <div style='background: #F8F9FA; padding: 25px; border-radius: 8px; border: 1px solid #E0E0E0;'>
                    <div style='font-size: 16px; color: #2D2D2D; line-height: 1.8;'>
                        " . $personas_detalle . "
                    </div>
                    <div style='margin-top: 20px; padding-top: 20px; border-top: 2px solid #EFCF4B; text-align: center;'>
                        <p style='margin: 0; font-weight: 700; color: #871727; font-size: 18px;'>Total personas con plaza: " . $reserva['total_personas'] . "</p>
                    </div>
                </div>
            </div>

            <!-- Acciones recomendadas -->
            <div style='padding: 40px 30px; background: #FFFFFF;'>
                <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Acciones Recomendadas</h3>
                
                <div style='background: #F8F9FA; padding: 30px; border-radius: 8px; border-left: 4px solid #EFCF4B;'>
                    <ul style='margin: 0; padding-left: 25px; color: #2D2D2D; line-height: 1.8; font-size: 16px;'>
                        <li style='margin: 12px 0;'><strong style='color: #871727;'>Verificar disponibilidad:</strong> Comprobar plazas disponibles para la fecha</li>
                        <li style='margin: 12px 0;'><strong style='color: #871727;'>Revisar documentación:</strong> Confirmar documentos de residentes si aplica</li>
                        <li style='margin: 12px 0;'><strong style='color: #871727;'>Gestionar reserva:</strong> Acceder al panel de administración</li>
                        <li style='margin: 12px 0;'><strong style='color: #871727;'>Contactar cliente:</strong> Si necesitas aclarar algún detalle</li>
                    </ul>
                </div>
                
                <!-- Mensaje final -->
                <div style='text-align: center; margin-top: 40px; padding: 30px; background: #871727; border-radius: 8px;'>
                    <p style='margin: 0; color: #FFFFFF; font-size: 20px; font-weight: 700;'>
                        Reserva lista para procesar
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style='text-align: center; padding: 40px 30px; background: #2D2D2D; color: #FFFFFF;'>
            <div style='width: 40px; height: 2px; background: #EFCF4B; margin: 0 auto 20px;'></div>
            <p style='margin: 0 0 15px 0; font-size: 14px; opacity: 0.8; line-height: 1.6;'>
                Este es un email automático del sistema de reservas.<br>
                Puedes gestionar esta reserva desde el panel de administración.
            </p>
            <p style='margin: 0; color: #EFCF4B; font-weight: 600; font-size: 16px;'>
                Sistema de Reservas - Medina Azahara
            </p>
        </div>

    </body>
    </html>";
    }

    /**
     * Reenviar email de confirmación
     */
    public static function resend_confirmation($reserva_id)
    {
        global $wpdb;

        $table_reservas = $wpdb->prefix . 'reservas_reservas';

        $reserva = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_reservas WHERE id = %d",
            $reserva_id
        ));

        if (!$reserva) {
            return array('success' => false, 'message' => 'Reserva no encontrada');
        }

        // Convertir objeto a array para el template
        $reserva_array = (array) $reserva;

        return self::send_customer_confirmation($reserva_array);
    }

    public static function send_cancellation_email($reserva_data)
    {
        $config = self::get_email_config();

        $to = $reserva_data['email'];
        $subject = "Reserva Cancelada - Localizador: " . $reserva_data['localizador'];

        $message = self::build_cancellation_email_template($reserva_data);

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $config['nombre_remitente'] . ' <' . $config['email_remitente'] . '>'
        );

        $sent = wp_mail($to, $subject, $message, $headers);

        if ($sent) {
            error_log("✅ Email de cancelación enviado al cliente: " . $to);
            return array('success' => true, 'message' => 'Email de cancelación enviado correctamente');
        } else {
            error_log("❌ Error enviando email de cancelación al cliente: " . $to);
            return array('success' => false, 'message' => 'Error enviando email de cancelación');
        }
    }

    private static function build_cancellation_email_template($reserva)
    {
        $fecha_formateada = date('d/m/Y', strtotime($reserva['fecha']));
        $motivo = $reserva['motivo_cancelacion'] ?? 'Cancelación administrativa';
        $fecha_cancelacion = date('d/m/Y H:i', strtotime($reserva['fecha_cancelacion'] ?? 'now'));

        return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Reserva Cancelada - Medina Azahara</title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        </style>
    </head>
    <body style='font-family: \"Inter\", -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; color: #2D2D2D; max-width: 600px; margin: 0 auto; padding: 0; background: #FAFAFA;'>
        
        <!-- Header -->
        <div style='background: linear-gradient(135deg, #871727 0%, #A91D33 100%); color: #FFFFFF; text-align: center; padding: 50px 30px;'>
            <h1 style='margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;'>RESERVA CANCELADA</h1>
            <div style='width: 60px; height: 3px; background: #EFCF4B; margin: 20px auto; border-radius: 2px;'></div>
            <p style='margin: 0; font-size: 18px; font-weight: 500; opacity: 0.95;'>Tu reserva ha sido cancelada</p>
        </div>

        <!-- Contenido principal -->
        <div style='background: #FFFFFF; padding: 0;'>
            
            <!-- Localizador destacado -->
            <div style='background: #EFCF4B; padding: 30px; text-align: center; border-bottom: 1px solid #E0E0E0;'>
                <h2 style='margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: #2D2D2D; text-transform: uppercase; letter-spacing: 1px;'>LOCALIZADOR CANCELADO</h2>
                <div style='font-size: 28px; font-weight: 700; color: #871727; letter-spacing: 3px; font-family: monospace; margin: 10px 0;'>" . $reserva['localizador'] . "</div>
                <p style='margin: 0; font-size: 14px; color: #2D2D2D; font-weight: 500;'>Estado: CANCELADA</p>
            </div>

            <!-- Información de la reserva cancelada -->
            <div style='padding: 40px 30px; border-bottom: 1px solid #E0E0E0;'>
                <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Detalles de la Reserva Cancelada</h3>
                
                <table style='width: 100%; border-collapse: collapse; background: #FFFFFF; border: 2px solid #EFCF4B; border-radius: 8px; overflow: hidden;'>
                    <tr>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Fecha del viaje:</td>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #871727;'>" . $fecha_formateada . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Hora de salida:</td>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #871727; font-size: 18px;'>" . substr($reserva['hora'], 0, 5) . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Cliente:</td>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; color: #666666;'>" . $reserva['nombre'] . " " . $reserva['apellidos'] . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Fecha de cancelación:</td>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; color: #871727; font-weight: 600;'>" . $fecha_cancelacion . "</td>
                    </tr>
                </table>
            </div>

            <!-- Motivo de cancelación -->
            <div style='padding: 40px 30px; background: #F8F9FA; border-bottom: 1px solid #E0E0E0;'>
                <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Motivo de la Cancelación</h3>
                
                <div style='background: #FFFFFF; padding: 25px; border-radius: 8px; border: 1px solid #E0E0E0; border-left: 4px solid #EFCF4B;'>
                    <p style='margin: 0; color: #2D2D2D; font-size: 16px; line-height: 1.6;'>" . $motivo . "</p>
                </div>
            </div>

            <!-- Información importante -->
            <div style='padding: 40px 30px; background: #FFFFFF;'>
                <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Información Importante</h3>
                
                <div style='background: #F8F9FA; padding: 30px; border-radius: 8px; border-left: 4px solid #EFCF4B;'>
                    <ul style='margin: 0; padding-left: 25px; color: #2D2D2D; line-height: 1.8; font-size: 16px;'>
                        <li style='margin: 12px 0;'><strong style='color: #871727;'>Nueva reserva:</strong> Puedes realizar una nueva reserva cuando lo desees</li>
                        <li style='margin: 12px 0;'><strong style='color: #871727;'>Reembolso:</strong> Si pagaste online, el reembolso se procesará según nuestras condiciones</li>
                        <li style='margin: 12px 0;'><strong style='color: #871727;'>Soporte:</strong> Para cualquier consulta, contacta con nuestro servicio de atención al cliente</li>
                        <li style='margin: 12px 0;'><strong style='color: #871727;'>Disculpas:</strong> Lamentamos las molestias ocasionadas por esta cancelación</li>
                    </ul>
                </div>
                
                <!-- Mensaje final -->
                <div style='text-align: center; margin-top: 40px; padding: 30px; background: #871727; border-radius: 8px;'>
                    <p style='margin: 0; color: #FFFFFF; font-size: 20px; font-weight: 700;'>
                        ¡Esperamos verte pronto en Medina Azahara!
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style='text-align: center; padding: 40px 30px; background: #2D2D2D; color: #FFFFFF;'>
            <div style='width: 40px; height: 2px; background: #EFCF4B; margin: 0 auto 20px;'></div>
            <p style='margin: 0 0 15px 0; font-size: 14px; opacity: 0.8; line-height: 1.6;'>
                Este es un email automático de notificación de cancelación.<br>
                Si tienes alguna duda, ponte en contacto con nosotros.
            </p>
            <p style='margin: 0; color: #EFCF4B; font-weight: 600; font-size: 16px;'>
                Gracias por tu comprensión
            </p>
        </div>

    </body>
    </html>";
    }


public static function send_admin_agency_reservation_notification($reserva_data, $admin_user)
{
    $config = self::get_email_config();

    // ✅ OBTENER EMAIL CORRECTO SEGÚN TIPO DE SERVICIO
    $superadmin_email = self::get_admin_email_by_service($reserva_data);

    $is_visita = isset($reserva_data['is_visita']) && $reserva_data['is_visita'] === true;
    $tipo_servicio = $is_visita ? 'Visita Guiada' : 'Autobús';

    $subject = "Reserva Rápida realizada por Administrador ({$tipo_servicio}) - " . $reserva_data['localizador'];

    $message = self::build_admin_agency_notification_template($reserva_data, $admin_user);

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $config['nombre_remitente'] . ' <' . $config['email_remitente'] . '>'
    );

    $sent = wp_mail($superadmin_email, $subject, $message, $headers);

    if ($sent) {
        error_log("✅ Email de notificación enviado al super_admin sobre reserva de administrador ({$tipo_servicio})");
        return array('success' => true, 'message' => 'Email enviado al super_admin');
    } else {
        error_log("❌ Error enviando email al super_admin sobre reserva de administrador ({$tipo_servicio})");
        return array('success' => false, 'message' => 'Error enviando email al super_admin');
    }
}

    /**
     * Template de email para notificar al super_admin sobre reserva hecha por administrador
     */
    private static function build_admin_agency_notification_template($reserva, $admin_user)
    {
        $fecha_formateada = date('d/m/Y', strtotime($reserva['fecha']));
        $fecha_creacion = date('d/m/Y H:i', strtotime($reserva['created_at'] ?? 'now'));

        $personas_detalle = "";
        if ($reserva['adultos'] > 0) $personas_detalle .= "Adultos: " . $reserva['adultos'] . "<br>";
        if ($reserva['residentes'] > 0) $personas_detalle .= "Residentes: " . $reserva['residentes'] . "<br>";
        if ($reserva['ninos_5_12'] > 0) $personas_detalle .= "Niños (5-12 años): " . $reserva['ninos_5_12'] . "<br>";
        if ($reserva['ninos_menores'] > 0) $personas_detalle .= "Niños menores (gratis): " . $reserva['ninos_menores'] . "<br>";

        $descuento_info = "";
        if ($reserva['descuento_total'] > 0) {
            $descuento_info = "<tr>
        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; background: #FFF8DC; font-weight: 600; color: #871727;'>Descuentos aplicados:</td>
        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; background: #FFF8DC; text-align: right; color: #871727; font-weight: bold; font-size: 16px;'>-" . number_format($reserva['descuento_total'], 2) . "€</td>
    </tr>";
        }

        // Determinar tipo de usuario
        $admin_role_text = '';
        switch ($admin_user['role']) {
            case 'super_admin':
                $admin_role_text = 'Super Administrador';
                break;
            case 'admin':
                $admin_role_text = 'Administrador';
                break;
            default:
                $admin_role_text = ucfirst($admin_user['role']);
        }

        return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Reserva Rápida por Administrador - Sistema Medina Azahara</title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        </style>
    </head>
    <body style='font-family: \"Inter\", -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; color: #2D2D2D; max-width: 700px; margin: 0 auto; padding: 0; background: #FAFAFA;'>
        
        <!-- Header Administrativo -->
        <div style='background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: #FFFFFF; text-align: center; padding: 50px 30px;'>
            <h1 style='margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;'>RESERVA RÁPIDA REALIZADA</h1>
            <div style='width: 60px; height: 3px; background: #EFCF4B; margin: 20px auto; border-radius: 2px;'></div>
            <p style='margin: 0; font-size: 18px; font-weight: 500; opacity: 0.95;'>Un administrador ha procesado una nueva reserva</p>
        </div>

        <!-- Contenido principal -->
        <div style='background: #FFFFFF; padding: 0;'>
            
            <!-- Información del Administrador -->
            <div style='background: #E8F5E8; padding: 30px; border-bottom: 1px solid #E0E0E0;'>
                <h2 style='margin: 0 0 15px 0; font-size: 18px; font-weight: 700; color: #28a745; text-align: center;'>INFORMACIÓN DEL ADMINISTRADOR</h2>
                
                <div style='background: #FFFFFF; padding: 25px; border-radius: 8px; border: 2px solid #28a745;'>
                    <div style='display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;'>
                        <div style='flex: 1; min-width: 200px;'>
                            <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #28a745;'>Administrador:</strong> " . esc_html($admin_user['username']) . "</p>
                            <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #28a745;'>Rol:</strong> " . $admin_role_text . "</p>
                        </div>
                        <div style='flex: 1; min-width: 200px; text-align: right;'>
                            <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #28a745;'>Fecha procesamiento:</strong> " . $fecha_creacion . "</p>
                            <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #28a745;'>Método:</strong> Reserva Rápida</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Localizador destacado -->
            <div style='background: #EFCF4B; padding: 30px; text-align: center; border-bottom: 1px solid #E0E0E0;'>
                <h2 style='margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: #2D2D2D; text-transform: uppercase; letter-spacing: 1px;'>LOCALIZADOR DE RESERVA</h2>
                <div style='font-size: 28px; font-weight: 700; color: #871727; letter-spacing: 3px; font-family: monospace; margin: 10px 0;'>" . $reserva['localizador'] . "</div>
                <p style='margin: 0; font-size: 14px; color: #2D2D2D; font-weight: 500;'>Reserva procesada por administrador</p>
            </div>

            <!-- Información de la reserva -->
            <div style='padding: 40px 30px; border-bottom: 1px solid #E0E0E0;'>
                <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Información de la Reserva</h3>
                
                <table style='width: 100%; border-collapse: collapse; background: #FFFFFF; border: 2px solid #EFCF4B; border-radius: 8px; overflow: hidden;'>
                    <tr>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Fecha del servicio:</td>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #871727;'>" . $fecha_formateada . " a las " . substr($reserva['hora'], 0, 5) . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Fecha de creación:</td>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; color: #666666;'>" . $fecha_creacion . "</td>
                    </tr>
                    <tr>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Total personas:</td>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #871727; font-size: 18px;'>" . $reserva['total_personas'] . " plazas ocupadas</td>
                    </tr>
                    <tr>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Precio base:</td>
                        <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 600; color: #2D2D2D;'>" . number_format($reserva['precio_base'], 2) . "€</td>
                    </tr>
                    " . $descuento_info . "
                    <tr style='background: #28a745;'>
                        <td style='padding: 20px 25px; font-size: 20px; font-weight: 700; color: #FFFFFF;'>TOTAL PROCESADO:</td>
                        <td style='padding: 20px 25px; text-align: right; font-size: 24px; font-weight: 700; color: #FFFFFF;'>" . number_format($reserva['precio_final'], 2) . "€</td>
                    </tr>
                </table>
            </div>

            <!-- Datos del cliente -->
            <div style='padding: 40px 30px; background: #F8F9FA; border-bottom: 1px solid #E0E0E0;'>
                <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Datos del Cliente</h3>
                
                <div style='background: #FFFFFF; padding: 25px; border-radius: 8px; border: 1px solid #E0E0E0;'>
                    <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #871727;'>Nombre completo:</strong> " . $reserva['nombre'] . " " . $reserva['apellidos'] . "</p>
                    <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #871727;'>Email:</strong> <a href='mailto:" . $reserva['email'] . "' style='color: #871727; text-decoration: none;'>" . $reserva['email'] . "</a></p>
                    <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #871727;'>Teléfono:</strong> <a href='tel:" . $reserva['telefono'] . "' style='color: #871727; text-decoration: none;'>" . $reserva['telefono'] . "</a></p>
                </div>
            </div>

            <!-- Distribución de personas -->
            <div style='padding: 40px 30px; border-bottom: 1px solid #E0E0E0;'>
                <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Distribución de Viajeros</h3>
                
                <div style='background: #F8F9FA; padding: 25px; border-radius: 8px; border: 1px solid #E0E0E0;'>
                    <div style='font-size: 16px; color: #2D2D2D; line-height: 1.8;'>
                        " . $personas_detalle . "
                    </div>
                    <div style='margin-top: 20px; padding-top: 20px; border-top: 2px solid #EFCF4B; text-align: center;'>
                        <p style='margin: 0; font-weight: 700; color: #871727; font-size: 18px;'>Total personas con plaza: " . $reserva['total_personas'] . "</p>
                    </div>
                </div>
            </div>

            <!-- Información importante -->
            <div style='padding: 40px 30px; background: #FFFFFF;'>
                <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #28a745; text-align: center;'>Información Importante</h3>
                
                <div style='background: #E8F5E8; padding: 30px; border-radius: 8px; border-left: 4px solid #28a745;'>
                    <ul style='margin: 0; padding-left: 25px; color: #2D2D2D; line-height: 1.8; font-size: 16px;'>
                        <li style='margin: 12px 0;'><strong style='color: #28a745;'>Reserva procesada por:</strong> " . esc_html($admin_user['username']) . " (" . $admin_role_text . ")</li>
                        <li style='margin: 12px 0;'><strong style='color: #28a745;'>Estado de la reserva:</strong> Confirmada automáticamente</li>
                        <li style='margin: 12px 0;'><strong style='color: #28a745;'>Email enviado al cliente:</strong> Sí, con billete PDF adjunto</li>
                        <li style='margin: 12px 0;'><strong style='color: #28a745;'>Plazas actualizadas:</strong> Automáticamente descontadas del servicio</li>
                        <li style='margin: 12px 0;'><strong style='color: #28a745;'>Gestión desde panel:</strong> Disponible en la sección de Informes y Reservas</li>
                    </ul>
                </div>
                
                <!-- Acciones disponibles -->
                <div style='background: #F8F9FA; padding: 25px; border-radius: 8px; margin-top: 20px; border: 1px solid #E0E0E0;'>
                    <h4 style='margin: 0 0 15px 0; color: #28a745; font-size: 16px;'>Acciones Disponibles:</h4>
                    <ul style='margin: 0; padding-left: 20px; color: #2D2D2D; line-height: 1.6;'>
                        <li>Buscar la reserva por localizador: <strong>" . $reserva['localizador'] . "</strong></li>
                        <li>Reenviar email de confirmación al cliente si es necesario</li>
                        <li>Cancelar la reserva desde el panel de administración</li>
                        <li>Ver estadísticas y reportes del administrador</li>
                    </ul>
                </div>
                
                <!-- Mensaje final -->
                <div style='text-align: center; margin-top: 40px; padding: 30px; background: #28a745; border-radius: 8px;'>
                    <p style='margin: 0; color: #FFFFFF; font-size: 20px; font-weight: 700;'>
                        Reserva procesada exitosamente
                    </p>
                    <p style='margin: 10px 0 0 0; color: #FFFFFF; font-size: 16px; opacity: 0.9;'>
                        El cliente ha recibido su confirmación por email
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style='text-align: center; padding: 40px 30px; background: #2D2D2D; color: #FFFFFF;'>
            <div style='width: 40px; height: 2px; background: #28a745; margin: 0 auto 20px;'></div>
            <p style='margin: 0 0 15px 0; font-size: 14px; opacity: 0.8; line-height: 1.6;'>
                Esta es una notificación automática de reserva rápida procesada por administrador.<br>
                Puedes gestionar esta reserva desde el panel de administración.
            </p>
            <p style='margin: 0; color: #28a745; font-weight: 600; font-size: 16px;'>
                Sistema de Reservas - Medina Azahara
            </p>
        </div>

    </body>
    </html>";
    }


 public static function send_agency_reservation_notification($reserva_data, $agency_user)
{
    $config = self::get_email_config();

    // ✅ OBTENER EMAIL CORRECTO SEGÚN TIPO DE SERVICIO
    $superadmin_email = self::get_admin_email_by_service($reserva_data);

    $is_visita = isset($reserva_data['is_visita']) && $reserva_data['is_visita'] === true;
    $tipo_servicio = $is_visita ? 'Visita Guiada' : 'Autobús';

    $subject = "Reserva Rápida realizada por Agencia ({$tipo_servicio}) - " . $reserva_data['localizador'];

    $message = self::build_agency_reservation_notification_template($reserva_data, $agency_user);

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $config['nombre_remitente'] . ' <' . $config['email_remitente'] . '>'
    );

    $sent = wp_mail($superadmin_email, $subject, $message, $headers);

    if ($sent) {
        error_log("✅ Email enviado al super_admin sobre reserva de agencia ({$tipo_servicio})");
        return array('success' => true, 'message' => 'Email enviado al super_admin');
    } else {
        error_log("❌ Error enviando email al super_admin sobre reserva de agencia ({$tipo_servicio})");
        return array('success' => false, 'message' => 'Error enviando email al super_admin');
    }
}

    /**
     * Enviar email a la propia agencia sobre su reserva
     */
    public static function send_agency_self_notification($reserva_data, $agency_user)
    {
        $config = self::get_email_config();

        // ✅ OBTENER EMAIL DE NOTIFICACIONES DE LA AGENCIA
        $agency_email = null;

        if (is_array($agency_user)) {
            $agency_email = !empty($agency_user['email_notificaciones']) ?
                $agency_user['email_notificaciones'] :
                $agency_user['email'];
        } else {
            // Si es objeto
            $agency_email = !empty($agency_user->email_notificaciones) ?
                $agency_user->email_notificaciones :
                $agency_user->email;
        }

        if (empty($agency_email)) {
            error_log("❌ No hay email configurado para la agencia");
            return array('success' => false, 'message' => 'Email de agencia no configurado');
        }

        error_log("📧 Enviando email a agencia: " . $agency_email);

        $agency_name = is_array($agency_user) ? $agency_user['agency_name'] : $agency_user->agency_name;
        $subject = "Confirmación de Reserva Rápida - " . $reserva_data['localizador'] . " - " . $agency_name;

        $message = self::build_agency_self_notification_template($reserva_data, $agency_user);

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $config['nombre_remitente'] . ' <' . $config['email_remitente'] . '>'
        );

        $sent = wp_mail($agency_email, $subject, $message, $headers);

        if ($sent) {
            error_log("✅ Email enviado a la agencia: " . $agency_email);
            return array('success' => true, 'message' => 'Email enviado a la agencia');
        } else {
            error_log("❌ Error enviando email a la agencia: " . $agency_email);
            return array('success' => false, 'message' => 'Error enviando email a la agencia');
        }
    }

    /**
     * Template para notificar al super_admin sobre reserva de agencia
     */
    private static function build_agency_reservation_notification_template($reserva, $agency_user)
    {
        $fecha_formateada = date('d/m/Y', strtotime($reserva['fecha']));
        $fecha_creacion = date('d/m/Y H:i', strtotime($reserva['created_at'] ?? 'now'));

        $personas_detalle = "";
        if ($reserva['adultos'] > 0) $personas_detalle .= "Adultos: " . $reserva['adultos'] . "<br>";
        if ($reserva['residentes'] > 0) $personas_detalle .= "Residentes: " . $reserva['residentes'] . "<br>";
        if ($reserva['ninos_5_12'] > 0) $personas_detalle .= "Niños (5-12 años): " . $reserva['ninos_5_12'] . "<br>";
        if ($reserva['ninos_menores'] > 0) $personas_detalle .= "Niños menores (gratis): " . $reserva['ninos_menores'] . "<br>";

        return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Reserva de Agencia - Sistema Medina Azahara</title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        </style>
    </head>
    <body style='font-family: \"Inter\", -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; color: #2D2D2D; max-width: 700px; margin: 0 auto; padding: 0; background: #FAFAFA;'>
        
        <!-- Header -->
        <div style='background: linear-gradient(135deg, #0073aa 0%, #005177 100%); color: #FFFFFF; text-align: center; padding: 50px 30px;'>
            <h1 style='margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;'>RESERVA RÁPIDA DE AGENCIA</h1>
            <div style='width: 60px; height: 3px; background: #EFCF4B; margin: 20px auto; border-radius: 2px;'></div>
            <p style='margin: 0; font-size: 18px; font-weight: 500; opacity: 0.95;'>Una agencia ha procesado una nueva reserva</p>
        </div>

        <!-- Información de la Agencia -->
        <div style='background: #E8F4F8; padding: 30px; border-bottom: 1px solid #E0E0E0;'>
            <h2 style='margin: 0 0 15px 0; font-size: 18px; font-weight: 700; color: #0073aa; text-align: center;'>🏢 INFORMACIÓN DE LA AGENCIA</h2>
            
            <div style='background: #FFFFFF; padding: 25px; border-radius: 8px; border: 2px solid #0073aa;'>
                <div style='display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;'>
                    <div style='flex: 1; min-width: 200px;'>
                        <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #0073aa;'>Agencia:</strong> " . esc_html($agency_user['agency_name']) . "</p>
                        <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #0073aa;'>Usuario:</strong> " . esc_html($agency_user['username']) . "</p>
                    </div>
                    <div style='flex: 1; min-width: 200px; text-align: right;'>
                        <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #0073aa;'>Email:</strong> " . esc_html($agency_user['email']) . "</p>
                        <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #0073aa;'>Comisión:</strong> " . number_format($agency_user['commission_percentage'], 1) . "%</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Localizador destacado -->
        <div style='background: #EFCF4B; padding: 30px; text-align: center; border-bottom: 1px solid #E0E0E0;'>
            <h2 style='margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: #2D2D2D; text-transform: uppercase; letter-spacing: 1px;'>LOCALIZADOR DE RESERVA</h2>
            <div style='font-size: 28px; font-weight: 700; color: #871727; letter-spacing: 3px; font-family: monospace; margin: 10px 0;'>" . $reserva['localizador'] . "</div>
            <p style='margin: 0; font-size: 14px; color: #2D2D2D; font-weight: 500;'>Reserva procesada por agencia</p>
        </div>

        <!-- Información de la reserva -->
        <div style='padding: 40px 30px; border-bottom: 1px solid #E0E0E0;'>
            <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Información de la Reserva</h3>
            
            <table style='width: 100%; border-collapse: collapse; background: #FFFFFF; border: 2px solid #EFCF4B; border-radius: 8px; overflow: hidden;'>
                <tr>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Fecha del servicio:</td>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #871727;'>" . $fecha_formateada . " a las " . substr($reserva['hora'], 0, 5) . "</td>
                </tr>
                <tr>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Total personas:</td>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #871727; font-size: 18px;'>" . $reserva['total_personas'] . " plazas ocupadas</td>
                </tr>
                <tr style='background: #0073aa;'>
                    <td style='padding: 20px 25px; font-size: 20px; font-weight: 700; color: #FFFFFF;'>TOTAL PROCESADO:</td>
                    <td style='padding: 20px 25px; text-align: right; font-size: 24px; font-weight: 700; color: #FFFFFF;'>" . number_format($reserva['precio_final'], 2) . "€</td>
                </tr>
            </table>
        </div>

        <!-- Datos del cliente -->
        <div style='padding: 40px 30px; background: #F8F9FA; border-bottom: 1px solid #E0E0E0;'>
            <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #871727; text-align: center;'>Datos del Cliente</h3>
            
            <div style='background: #FFFFFF; padding: 25px; border-radius: 8px; border: 1px solid #E0E0E0;'>
                <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #871727;'>Cliente:</strong> " . $reserva['nombre'] . " " . $reserva['apellidos'] . "</p>
                <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #871727;'>Email:</strong> " . $reserva['email'] . "</p>
                <p style='margin: 8px 0; color: #2D2D2D; font-size: 16px;'><strong style='color: #871727;'>Teléfono:</strong> " . $reserva['telefono'] . "</p>
            </div>
        </div>

        <!-- Información importante -->
        <div style='padding: 40px 30px; background: #FFFFFF;'>
            <div style='background: #E8F4F8; padding: 30px; border-radius: 8px; border-left: 4px solid #0073aa;'>
                <ul style='margin: 0; padding-left: 25px; color: #2D2D2D; line-height: 1.8; font-size: 16px;'>
                    <li style='margin: 12px 0;'><strong style='color: #0073aa;'>Reserva procesada por:</strong> " . esc_html($agency_user['agency_name']) . "</li>
                    <li style='margin: 12px 0;'><strong style='color: #0073aa;'>Estado:</strong> Confirmada automáticamente</li>
                    <li style='margin: 12px 0;'><strong style='color: #0073aa;'>Emails enviados:</strong> Cliente y agencia notificados</li>
                    <li style='margin: 12px 0;'><strong style='color: #0073aa;'>Comisión agencia:</strong> " . number_format($agency_user['commission_percentage'], 1) . "%</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div style='text-align: center; padding: 40px 30px; background: #2D2D2D; color: #FFFFFF;'>
            <p style='margin: 0; color: #0073aa; font-weight: 600; font-size: 16px;'>
                Sistema de Reservas - Medina Azahara
            </p>
        </div>
    </body>
    </html>";
    }

    /**
     * Template para notificar a la agencia sobre su propia reserva
     */
    private static function build_agency_self_notification_template($reserva, $agency_user)
{
    $fecha_formateada = date('d/m/Y', strtotime($reserva['fecha']));
    $fecha_creacion = date('d/m/Y H:i', strtotime($reserva['created_at'] ?? 'now'));

    // ✅ PREPARAR DETALLES DE PERSONAS SIN PRECIOS
    $personas_detalle = "";
    if ($reserva['adultos'] > 0) $personas_detalle .= "Adultos: " . $reserva['adultos'] . "<br>";
    if ($reserva['residentes'] > 0) $personas_detalle .= "Residentes: " . $reserva['residentes'] . "<br>";
    if ($reserva['ninos_5_12'] > 0) $personas_detalle .= "Niños (5-12 años): " . $reserva['ninos_5_12'] . "<br>";
    if ($reserva['ninos_menores'] > 0) $personas_detalle .= "Niños menores (gratis): " . $reserva['ninos_menores'] . "<br>";

    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Confirmación Reserva Rápida - " . esc_html($agency_user['agency_name']) . "</title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        </style>
    </head>
    <body style='font-family: \"Inter\", -apple-system, BlinkMacSystemFont, sans-serif; line-height: 1.6; color: #2D2D2D; max-width: 600px; margin: 0 auto; padding: 0; background: #FAFAFA;'>
        
        <!-- Header -->
        <div style='background: linear-gradient(135deg, #0073aa 0%, #005177 100%); color: #FFFFFF; text-align: center; padding: 50px 30px;'>
            <h1 style='margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -0.5px;'>RESERVA PROCESADA</h1>
            <div style='width: 60px; height: 3px; background: #EFCF4B; margin: 20px auto; border-radius: 2px;'></div>
            <p style='margin: 0; font-size: 18px; font-weight: 500; opacity: 0.95;'>" . esc_html($agency_user['agency_name']) . "</p>
        </div>

        <!-- Localizador -->
        <div style='background: #EFCF4B; padding: 30px; text-align: center; border-bottom: 1px solid #E0E0E0;'>
            <h2 style='margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: #2D2D2D; text-transform: uppercase; letter-spacing: 1px;'>LOCALIZADOR</h2>
            <div style='font-size: 28px; font-weight: 700; color: #871727; letter-spacing: 3px; font-family: monospace; margin: 10px 0;'>" . $reserva['localizador'] . "</div>
            <p style='margin: 0; font-size: 14px; color: #2D2D2D; font-weight: 500;'>Reserva confirmada</p>
        </div>

        <!-- Resumen SIN PRECIOS -->
        <div style='padding: 40px 30px; background: #FFFFFF;'>
            <h3 style='margin: 0 0 25px 0; font-size: 20px; font-weight: 700; color: #0073aa; text-align: center;'>Resumen de la Operación</h3>
            
            <table style='width: 100%; border-collapse: collapse; background: #FFFFFF; border: 2px solid #0073aa; border-radius: 8px; overflow: hidden;'>
                <tr>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Cliente:</td>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; color: #666666;'>" . $reserva['nombre'] . " " . $reserva['apellidos'] . "</td>
                </tr>
                <tr>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Fecha servicio:</td>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #0073aa;'>" . $fecha_formateada . " - " . substr($reserva['hora'], 0, 5) . "</td>
                </tr>
                <tr>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; font-weight: 600; color: #2D2D2D;'>Hora de vuelta:</td>
                    <td style='padding: 15px 25px; border-bottom: 1px solid #E0E0E0; text-align: right; font-weight: 700; color: #0073aa;'>" . substr($reserva['hora_vuelta'] ?? '', 0, 5) . "</td>
                </tr>
                <tr>
                    <td style='padding: 15px 25px; font-weight: 600; color: #2D2D2D;'>Total personas:</td>
                    <td style='padding: 15px 25px; text-align: right; font-weight: 700; color: #0073aa;'>" . $reserva['total_personas'] . "</td>
                </tr>
            </table>

            <!-- ✅ DISTRIBUCIÓN DE VIAJEROS SIN PRECIOS -->
            <div style='margin-top: 30px; padding: 25px; background: #F8F9FA; border-radius: 8px; border: 1px solid #E0E0E0;'>
                <h4 style='margin: 0 0 15px 0; color: #0073aa; font-size: 16px;'>Distribución de Viajeros:</h4>
                <div style='font-size: 16px; color: #2D2D2D; line-height: 1.8;'>
                    " . $personas_detalle . "
                </div>
                <div style='margin-top: 15px; padding-top: 15px; border-top: 2px solid #0073aa; text-align: center;'>
                    <p style='margin: 0; font-weight: 700; color: #0073aa; font-size: 18px;'>Total plazas ocupadas: " . $reserva['total_personas'] . "</p>
                </div>
            </div>

            <div style='background: #E8F4F8; padding: 25px; border-radius: 8px; margin-top: 25px; border-left: 4px solid #0073aa;'>
                <h4 style='margin: 0 0 15px 0; color: #0073aa;'>✅ Acciones Completadas:</h4>
                <ul style='margin: 0; padding-left: 20px; color: #2D2D2D;'>
                    <li>Reserva confirmada automáticamente</li>
                    <li>Email enviado al cliente con billete PDF</li>
                    <li>Plazas actualizadas en el sistema</li>
                    <li>Administración notificada</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div style='text-align: center; padding: 40px 30px; background: #2D2D2D; color: #FFFFFF;'>
            <p style='margin: 0; color: #0073aa; font-weight: 600; font-size: 16px;'>
                Gracias por usar nuestro sistema
            </p>
        </div>
    </body>
    </html>";
}
}
