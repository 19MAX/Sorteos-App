# Uso de Colas (Queues)

## Introducción

El sistema utiliza colas para manejar tareas en segundo plano, como el envío de correos electrónicos, evitando ralentizar la ejecución principal de la aplicación.

---

## Implementación

Se utiliza el servicio de colas de CodeIgniter para enviar tareas de forma asíncrona.

### Envío a la cola

Ejemplo de envío de un mensaje a la cola:

```php
service('queue')->push('emails', 'email', [
    'message' => 'Mensaje del correo desde la cola'
]);
```

### Parámetros

* **emails** → Nombre de la cola
* **email** → Tipo de tarea (job)
* **message** → Datos enviados al proceso

---

## Flujo de funcionamiento

1. El sistema genera un evento (ej: registro, ganador, notificación)
2. Se envía un job a la cola
3. Un worker procesa la cola
4. Se ejecuta la acción (ej: envío de correo)

---

## Caso de uso en el sistema de sorteos

Las colas se utilizan para:

* Notificar a los participantes
* Enviar correo al ganador del sorteo
* Enviar confirmaciones de registro
* Enviar los números de los boletos

---

## ▶️ Ejecución del worker

Para procesar la cola:

```bash
php spark queue:work --nombre cola
```

---

## ⚠️ Recomendaciones

* Usar colas para tareas pesadas o repetitivas
* No ejecutar procesos largos directamente en controladores
* Supervisar workers en producción

---

## 🚧 Mejoras futuras

* Implementar reintentos automáticos
* Manejo de errores en jobs
* Logs de ejecución
