import makeWASocket, { 
    useMultiFileAuthState, 
    DisconnectReason, 
    fetchLatestBaileysVersion 
} from '@whiskeysockets/baileys'
import { Boom } from '@hapi/boom'
import qrcode from 'qrcode-terminal'
import axios from 'axios'

async function connectToWhatsApp() {
    // 1. Configuración de autenticación (Carpeta de sesión)
    const { state, saveCreds } = await useMultiFileAuthState('auth_info_baileys')
    
    // 2. Obtener versión de WhatsApp
    const { version } = await fetchLatestBaileysVersion()

    const sock = makeWASocket({
        version,
        auth: state,
        printQRInTerminal: false, // Lo manejamos manualmente abajo para que no se corte
        browser: ["Windows", "Chrome", "110.0.5481.178"],
        shouldSyncHistoryMessage: () => false, // Evita error 405 y lentitud
        connectTimeoutMs: 60000,
        defaultQueryTimeoutMs: 0,
        keepAliveIntervalMs: 10000,
    })

    // 🔐 Guardar credenciales
    sock.ev.on('creds.update', saveCreds)

    // 📡 Gestión de Conexión
    sock.ev.on('connection.update', (update) => {
        const { connection, lastDisconnect, qr } = update

        if (qr) {
            console.clear(); // Limpia la pantalla para que el QR salga arriba
            console.log('\n\n' + ' '.repeat(5) + '📱 ESCANEA EL QR DE REAL POTOSÍ');
            console.log(' '.repeat(5) + '---------------------------------');
            // Agregamos espacios laterales para que el lector del cel lo reconozca mejor
            qrcode.generate(qr, { small: true }); 
            console.log('\n' + ' '.repeat(5) + 'Si se ve cortado, reduce el zoom (Ctrl + Rueda)');
        }

        if (connection === 'open') {
            console.log('\n✅ ¡Bot Conectado Correctamente!');
        }

        if (connection === 'close') {
            const statusCode = (lastDisconnect?.error instanceof Boom) 
                ? lastDisconnect.error.output.statusCode 
                : 0

            console.log(`❌ Conexión cerrada (Código: ${statusCode})`);

            if (statusCode === DisconnectReason.loggedOut) {
                console.log('🛑 Sesión cerrada en el celular. Borra la carpeta "auth_info_baileys".');
                process.exit(1);
            } else {
                console.log('⏳ Reconectando en 5 segundos...');
                setTimeout(() => connectToWhatsApp(), 5000);
            }
        }
    })

    // 📩 Escuchar mensajes e integrar con Laravel
    sock.ev.on('messages.upsert', async (m) => {
        const msg = m.messages[0];
        if (!msg.message || msg.key.fromMe) return;

        const texto = msg.message.conversation || msg.message.extendedTextMessage?.text;
        const from = msg.key.remoteJid;

        if (texto) {
            console.log(`📨 Recibido de ${from}: ${texto}`);

            try {
                // Envío a tu API en Laravel
                const response = await axios.post('http://127.0.0.1:8000/api/bot/consultar', {
                    telefono: from,
                    mensaje: texto
                });

                const data = response.data;

                // 1. Enviar respuesta de texto (Si existe)
                if (data && data.respuesta) {
                    await sock.sendMessage(from, { 
                        text: data.respuesta 
                    });
                }

                // 2. ENVIAR IMÁGENES (Si existen en el array media)
                if (data && data.media && Array.isArray(data.media)) {
                    for (const urlImagen of data.media) {
                        console.log(`📸 Enviando imagen de Drive: ${urlImagen}`);
                        await sock.sendMessage(from, { 
                            image: { url: urlImagen },
                            caption: "Información Real Potosí 🦁" // Opcional: un pie de foto
                        });
                    }
                }

            } catch (error) {
                console.error('❌ Error Laravel:', error.message);
            }
        }
    })
}

// Arrancar el proceso
connectToWhatsApp().catch(err => console.error("Error crítico:", err));