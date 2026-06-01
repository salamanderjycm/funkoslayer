# Integración de MercadoPago - Guía de Configuración

## 📋 Estado de la Integración

✅ **Backend Laravel**: Completamente integrado
✅ **Frontend Vue 3**: SDK MercadoPago incluido
✅ **API endpoints**: Listos para procesar pagos
✅ **Rutas**: Configuradas

## 🔧 Pasos para Completar la Configuración

### Paso 1: Crear Cuenta en MercadoPago

1. Ve a https://www.mercadopago.com/developers/
2. Haz clic en **"Crear cuenta de vendedor"**
3. Completa el registro con tus datos
4. Verifica tu email

### Paso 2: Obtener Credenciales API

Una vez registrado en el Dashboard:

1. Ve a https://www.mercadopago.com.ar/developers/panel (o tu país)
2. Navega a **"Credenciales"** 
3. Encontrarás dos tipos de credenciales:

#### Para Desarrollo (Sandbox):
- **Public Key (Sandbox)**: `TEST-...` 
- **Access Token (Sandbox)**: `TEST-...`

#### Para Producción (Real):
- **Public Key (Producción)**: `APP_USR-...`
- **Access Token (Producción)**: `APP_USR-...`

### Paso 3: Configurar Variables de Entorno

Abre el archivo `.env` en la raíz del proyecto y actualiza:

```env
# Para TESTING (Sandbox)
MERCADOPAGO_PUBLIC_KEY=tu_public_key_sandbox_aqui
MERCADOPAGO_ACCESS_TOKEN=tu_access_token_sandbox_aqui
MERCADOPAGO_ENV=sandbox

# Para PRODUCCIÓN (cambiar cuando esté listo)
# MERCADOPAGO_PUBLIC_KEY=tu_public_key_produccion_aqui
# MERCADOPAGO_ACCESS_TOKEN=tu_access_token_produccion_aqui
# MERCADOPAGO_ENV=production
```

### Paso 4: Tarjetas de Prueba (Sandbox)

Para probar la integración sin dinero real, usa estas tarjetas:

#### Tarjeta Aprobada:
- **Número**: 4111111111111111
- **Vencimiento**: 11/25 (cualquier fecha futura)
- **CVV**: 123
- **Titular**: JUAN GARCIA

#### Tarjeta Rechazada:
- **Número**: 5105105105105100
- **Vencimiento**: 11/25
- **CVV**: 123

#### Tarjeta Pendiente:
- **Número**: 378282246310005
- **Vencimiento**: 11/25
- **CVV**: 123

## 🚀 Endpoints Disponibles

### Para el Frontend (Vue 3):

```javascript
// Obtener public key
GET /api/payment/public-key

// Crear preferencia de pago (Checkout Modal)
POST /api/payment/preference
Body: {
  items: [ { title, quantity, unit_price } ],
  payer: { name, email, phone, address },
  external_reference: "order_123"
}

// Crear pago directo (Tarjeta de Crédito)
POST /api/payment/create
Body: {
  token: "token_tarjeta",
  payment_method_id: "master",
  installments: 1,
  amount: 100.00,
  description: "Compra",
  payer_email: "cliente@example.com",
  external_reference: "order_123"
}

// Obtener estado de pago
GET /api/payment/{payment_id}/status
```

## 📱 Rutas de Callback

El sistema automáticamente redirige a:
- `http://localhost:8000/api/payment/success` - Pago exitoso
- `http://localhost:8000/api/payment/pending` - Pago pendiente
- `http://localhost:8000/api/payment/failure` - Pago rechazado

## 💳 Controlador de Pagos

Ubicación: `app/Http/Controllers/PaymentController.php`

Métodos principales:
- `createPreference()` - Crear preferencia de pago
- `createPayment()` - Procesar pago con tarjeta
- `getPaymentStatus()` - Consultar estado
- `handleWebhook()` - Recibir notificaciones de MercadoPago
- `getPublicKey()` - Obtener clave pública para frontend

## 🧪 Cómo Probar

1. **Inicia el servidor Laravel:**
```bash
php artisan serve
```

2. **En otra terminal, inicia Vite:**
```bash
pnpm dev
```

3. **Abre el navegador:**
```
http://localhost:8000
```

4. **Flujo de prueba:**
   - Inicia sesión con `customer@funkos.com` / `password123`
   - Agrega un producto al carrito
   - Abre el carrito y haz clic en "Proceder al Pago"
   - Completa el formulario
   - Usa una tarjeta de prueba
   - Haz clic en "Confirmar Pago"

## 📊 Monitoreo

### Ver transacciones en MercadoPago:
1. Ve a https://www.mercadopago.com.ar/balance
2. Selecciona "Movimientos"
3. Busca tus transacciones de prueba

### Logs del Sistema:
```bash
tail -f storage/logs/laravel.log
```

## 🔒 Seguridad

⚠️ **Importante:**
- Nunca compartas tu Access Token
- Usa variables de entorno para las credenciales
- En producción, siempre usa HTTPS
- Implementa validaciones en el backend
- No almacenes datos de tarjeta en tu base de datos

## 🐛 Troubleshooting

### Error: "Public key not found"
- Verifica que `MERCADOPAGO_PUBLIC_KEY` está en `.env`
- Reinicia el servidor Laravel después de cambiar `.env`

### Error: "Invalid token"
- Asegúrate de usar credenciales de Sandbox para pruebas
- Verifica que el token sea válido en MercadoPago

### Pago rechazado
- Usa las tarjetas de prueba correctas
- Verifica que la fecha de vencimiento sea futura
- Comprueba que el CVV sea 3-4 dígitos

## 📚 Recursos Útiles

- Documentación MercadoPago: https://www.mercadopago.com.ar/developers/es/docs
- SDK JavaScript: https://github.com/mercadopago/sdk-js
- Test Cards: https://www.mercadopago.com.ar/developers/es/docs/checkout-api/test-cards
- API Reference: https://www.mercadopago.com.ar/developers/es/docs/payment-api

## ✅ Checklist Final

- [ ] Registrado en MercadoPago
- [ ] Credenciales obtenidas
- [ ] `.env` actualizado con credenciales
- [ ] Servidor Laravel reiniciado
- [ ] Servidor Vite compilado (pnpm build)
- [ ] Probado con tarjeta de sandbox
- [ ] Transacción visible en MercadoPago Dashboard
- [ ] Webhook configurado en MercadoPago (opcional pero recomendado)

¡La integración está lista para usar! 🎉
