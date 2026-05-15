document.addEventListener("DOMContentLoaded", () => {
  const STORAGE_KEY = "quickluck_order";

  const steps = {
    1: document.getElementById("step-1"),
    2: document.getElementById("step-2"),
    3: document.getElementById("step-3"),
    4: document.getElementById("step-4"),
  };

  const formDatos = document.getElementById("form-datos");
  const qtyDisplay = document.getElementById("qty-display");
  const totalPrice = document.getElementById("total-price");

  let ORDER = {
    step: 1,
    qty: PRODUCT.minTickets,
    total: PRODUCT.ticketPrice,
    method: null,
  };

  // =====================
  // STORAGE
  // =====================
  const SESSION_DURATION = 30 * 60 * 1000; // 30 minutos

  function saveOrder() {
    const payload = {
      data: ORDER,
      expires: Date.now() + SESSION_DURATION,
    };
    sessionStorage.setItem("quickluck_order", JSON.stringify(payload));
  }

  function loadOrder() {
    const raw = sessionStorage.getItem("quickluck_order");
    if (!raw) return;
    const payload = JSON.parse(raw);
    if (Date.now() > payload.expires) {
      sessionStorage.removeItem("quickluck_order");
      return;
    }
    ORDER = payload.data;
    if (ORDER.step === 4) ORDER.step = 1;
  }

  function clearOrder() {
    sessionStorage.removeItem("quickluck_order");
    ORDER = {
      step: 1,
      qty: PRODUCT.minTickets,
      total: PRODUCT.ticketPrice * PRODUCT.minTickets,
      method: null,
      nombreLocked: false,
    };
  }

  function showStep4Loading() {
    goToStep(4);
    document.getElementById("step4-loading").classList.remove("hidden");
    document.getElementById("step4-success").classList.add("hidden");
  }

  function showStep4Success() {
    goToStep(4);
    document.getElementById("step4-loading").classList.add("hidden");
    document.getElementById("step4-success").classList.remove("hidden");
  }

  function showTxnInfo(data) {
    document.getElementById("txn-numero").textContent = data.numero_transaccion;
    document.getElementById("txn-boletos").textContent = data.boletos;
    document.getElementById("txn-expira").textContent = data.expira_en;
    document.getElementById("txn-info").classList.remove("hidden");
  }

  function resetFormToStep1() {
    clearOrder();
    document.querySelector('[name="nombre"]').value = "";
    document.querySelector('[name="nombre"]').readOnly = false;
    document.querySelector('[name="nombre"]').classList.remove('bg-gray-900', 'cursor-not-allowed');
    document.querySelector('[name="nombre"]').classList.add('focus:border-brand-gold');
    document.querySelector('[name="nombre"]').title = "";
    const badge = document.getElementById('nombre-locked-badge');
    if (badge) badge.remove();
    document.querySelector('[name="cedula"]').value = "";
    document.querySelector('[name="email"]').value = "";
    document.querySelector('[name="whatsapp"]').value = "";
    qtyDisplay.textContent = "1";
    updatePrice();
    document.querySelectorAll(".payment-card").forEach((c) => {
      c.classList.remove("payment-card-selected");
    });
    document.getElementById("bank-info").classList.add("hidden");
    document.getElementById("btn-goto-3").disabled = true;
    document
      .getElementById("btn-goto-3")
      .classList.add("bg-gray-800", "text-gray-500", "cursor-not-allowed");
    document
      .getElementById("btn-goto-3")
      .classList.remove("bg-brand-gold", "text-brand-dark");
    document.getElementById("terms").checked = false;
    document.getElementById("btn-finalizar").disabled = false;
    document.getElementById("btn-finalizar").innerHTML = "Confirmar →";
    document.getElementById("btn-finalizar").classList.remove("opacity-70");
    document.querySelectorAll(".bank-selector-btn").forEach((btn) => {
      btn.classList.remove("ring-2", "ring-brand-gold", "border-transparent");
      btn.classList.add("border-white/5");
    });
    document.querySelectorAll(".bank-detail-panel").forEach((panel) => {
      panel.classList.add("hidden");
    });
    document.getElementById("bank-empty-state").classList.remove("hidden");
    document.getElementById("step4-loading").classList.remove("hidden");
    document.getElementById("step4-success").classList.add("hidden");
    goToStep(1);
    document.getElementById("txn-info").classList.add("hidden");
  }

  document
    .getElementById("btn-volver-comprar")
    ?.addEventListener("click", () => {
      resetFormToStep1();
    });

  function updateSummary() {
    document.getElementById("summary-name").textContent = ORDER.nombre || "-";
    document.getElementById("summary-cedula").textContent = ORDER.cedula || "-";
    document.getElementById("summary-whatsapp").textContent =
      ORDER.whatsapp || "-";
    document.getElementById("summary-qty").textContent = ORDER.qty || 1;
    document.getElementById("summary-total").textContent =
      `$${(ORDER.total || 0).toFixed(2)}`;

    document.getElementById("summary-method").textContent =
      ORDER.method === "deposit"
        ? "Depósito / Transferencia"
        : ORDER.method === "card"
          ? "Tarjeta"
          : "-";
  }

  // =====================
  // VALIDACIONES
  // =====================

  function setError(input, message) {
    input.classList.add("input-error");
    input.classList.remove("input-success");

    const errorEl = document.querySelector(`[data-error="${input.name}"]`);
    errorEl.textContent = message;
    errorEl.classList.remove("hidden");
  }

  function clearError(input) {
    input.classList.remove("input-error");
    input.classList.add("input-success");

    const errorEl = document.querySelector(`[data-error="${input.name}"]`);
    errorEl.textContent = "";
    errorEl.classList.add("hidden");
  }

  function validarCampo(input) {
    const value = input.value.trim();

    // CÉDULA
    if (input.name === "cedula") {
      if (!value) return setError(input, "La cédula es obligatoria");
      if (!/^\d{10}$/.test(value))
        return setError(input, "Debe tener 10 números");
    }

    // TELÉFONO
    if (input.name === "whatsapp") {
      if (!value) return setError(input, "El teléfono es obligatorio");
      if (!/^\d{10}$/.test(value))
        return setError(input, "Debe tener 10 números");
    }

    // NOMBRE
    if (input.name === "nombre") {
      if (!value) return setError(input, "El nombre es obligatorio");
      if (value.length < 3) return setError(input, "Nombre muy corto");
    }

    // EMAIL
    if (input.name === "email") {
      if (!value) return setError(input, "El correo es obligatorio");

      const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!regex.test(value)) return setError(input, "Correo inválido");
    }

    clearError(input);
    return true;
  }

  const inputCedula = document.querySelector('[name="cedula"]');

  inputCedula.addEventListener("input", (e) => {
    // eliminar todo lo que no sea número
    let value = e.target.value.replace(/\D/g, "");

    // limitar a 10 caracteres
    value = value.slice(0, 10);

    e.target.value = value;

    // guardar en ORDER
    ORDER.cedula = value;
    saveOrder();
  });
  inputCedula.addEventListener("keypress", (e) => {
    if (!/[0-9]/.test(e.key)) {
      e.preventDefault();
    }
  });
  const inputPhone = document.querySelector('[name="whatsapp"]');

  inputPhone.addEventListener("input", (e) => {
    let value = e.target.value.replace(/\D/g, "").slice(0, 10);
    e.target.value = value;

    ORDER.whatsapp = value;
    saveOrder();
  });
  // =====================
  // UI
  // =====================
  function updatePrice() {
    ORDER.total = ORDER.qty * PRODUCT.ticketPrice;
    totalPrice.textContent = `$${ORDER.total.toFixed(2)}`;
  }

  function goToStep(step) {
    if (step === 3) updateSummary();
    Object.values(steps).forEach((s) => s.classList.add("hidden"));
    steps[step].classList.remove("hidden");
    ORDER.step = step;
    updateNav(step);
    saveOrder();
  }

  function updateNav(step) {
    document.querySelectorAll('[id^="nav-step-"]').forEach((el, idx) => {
      const dot = el.querySelector("div");
      const label = el.querySelector("span");

      dot.classList.remove("step-dot-active");
      label.classList.remove("step-active");

      if (idx + 1 <= step) {
        dot.classList.add("step-dot-active");
        label.classList.add("step-active");
      }
    });
  }

  function restoreUI() {
    // Inputs
    document.querySelector('[name="nombre"]').value = ORDER.nombre || "";
    document.querySelector('[name="cedula"]').value = ORDER.cedula || "";
    document.querySelector('[name="email"]').value = ORDER.email || "";
    document.querySelector('[name="whatsapp"]').value = ORDER.whatsapp || "";

    // Restore readonly state if nombre was locked
    const inputNombre = document.querySelector('[name="nombre"]');
    if (ORDER.nombreLocked) {
      inputNombre.readOnly = true;
      inputNombre.classList.add('bg-gray-900', 'cursor-not-allowed');
      inputNombre.classList.remove('focus:border-brand-gold');
      let badge = document.getElementById('nombre-locked-badge');
      if (!badge) {
        badge = document.createElement('span');
        badge.id = 'nombre-locked-badge';
        badge.className = 'inline-flex items-center gap-1 text-xs text-green-400 font-medium mt-1';
        badge.innerHTML = '✓ Nombre verificado';
        inputNombre.parentElement.appendChild(badge);
      }
    }

    // Qty
    qtyDisplay.textContent = ORDER.qty || 1;
    updatePrice();

    // Método
    if (ORDER.method) {
      document.querySelectorAll(".payment-card").forEach((c) => {
        c.classList.remove("payment-card-selected");
      });

      const card = document.querySelector(`[onclick*="${ORDER.method}"]`);
      if (card) card.classList.add("payment-card-selected");

      document
        .getElementById("bank-info")
        .classList.toggle("hidden", ORDER.method !== "deposit");

      enableContinue();
    }

    if (ORDER.step === 3) {
      updateSummary();
    }

    goToStep(ORDER.step || 1);
  }

  function enableContinue() {
    const btn = document.getElementById("btn-goto-3");
    btn.disabled = false;
    btn.classList.remove("bg-gray-800", "text-gray-500", "cursor-not-allowed");
    btn.classList.add("bg-brand-gold", "text-brand-dark");
  }

  // =====================
  // CONSULTA CÉDULA (AUTO)
  // =====================

  // debounce para no saturar API
  function debounce(fn, delay = 600) {
    let timeout;
    return (...args) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => fn(...args), delay);
    };
  }

  const inputNombre = document.querySelector('[name="nombre"]');
  const inputEmail = document.querySelector('[name="email"]');
  const inputWhatsapp = document.querySelector('[name="whatsapp"]');

  // función principal
  async function consultarCedula(cedula) {
    try {
      showSkeleton();

      const body = {
        cedula: cedula,
      };

      // agregar csrf dinámicamente
      body[CSRF.name] = CSRF.hash;

      const res = await fetch(baseUrl + "api/cedula", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(body),
      });

      const data = await res.json();

      // actualizar token SIEMPRE
      if (data.csrfHash) {
        CSRF.hash = data.csrfHash;
      }

      hideSkeleton();

      // If API returned an error (participant not found anywhere), allow manual entry
      if (data.error) {
        if (data.transaccion_id) {
          Swal.fire({
            icon: 'warning',
            title: 'Transacción Pendiente',
            html: `Ya tienes una transacción <strong>#${data.transaccion_id}</strong> con estado <strong>${data.status}</strong>.<br>Completa o cancela esa transacción antes de hacer una nueva compra.`,
            confirmButtonText: 'Entendido',
            background: '#111827',
            color: '#fff',
            confirmButtonColor: '#f5c518',
          });
          return;
        }
        inputNombre.readOnly = false;
        inputNombre.classList.remove('bg-gray-900', 'cursor-not-allowed');
        inputNombre.classList.add('focus:border-brand-gold');
        const badge = document.getElementById('nombre-locked-badge');
        if (badge) badge.remove();
        return;
      }

      if (!data.nombre && !data.apellidos) {
        return;
      }

      if (!inputNombre.value)
        inputNombre.value =
          (data.nombre || "") + (data.apellidos ? " " + data.apellidos : "");
      if (!inputEmail.value) inputEmail.value = data.email || "";
      if (!inputWhatsapp.value) inputWhatsapp.value = data.telefono || "";

      ORDER.nombre = inputNombre.value;
      ORDER.email = inputEmail.value;
      ORDER.whatsapp = inputWhatsapp.value;

      // Lock nombre field if participant exists in local DB or API has data
      if (data.locked) {
        inputNombre.readOnly = true;
        inputNombre.classList.add('bg-gray-900', 'cursor-not-allowed');
        inputNombre.classList.remove('focus:border-brand-gold');
        inputNombre.title = 'Nombre verificado desde registro oficial';
        ORDER.nombreLocked = true;

        let badge = document.getElementById('nombre-locked-badge');
        if (!badge) {
          badge = document.createElement('span');
          badge.id = 'nombre-locked-badge';
          badge.className = 'inline-flex items-center gap-1 text-xs text-green-400 font-medium mt-1';
          badge.innerHTML = '✓ Nombre verificado';
          inputNombre.parentElement.appendChild(badge);
        }
      } else {
        inputNombre.readOnly = false;
        inputNombre.classList.remove('bg-gray-900', 'cursor-not-allowed');
        inputNombre.classList.add('focus:border-brand-gold');
        inputNombre.title = '';
        ORDER.nombreLocked = false;
        const badge = document.getElementById('nombre-locked-badge');
        if (badge) badge.remove();
      }

      saveOrder();
    } catch (err) {
      console.error(err);
    } finally {
      hideSkeleton();
    }
  }

  // debounce aplicado
  const consultarDebounce = debounce((cedula) => {
    consultarCedula(cedula);
  }, 700);

  // evento input
  inputCedula.addEventListener("input", (e) => {
    const cedula = e.target.value.trim();

    // guardar en ORDER siempre
    ORDER.cedula = cedula;
    saveOrder();

    // validar solo números
    if (!/^\d+$/.test(cedula)) return;

    // solo cuando tenga 10 dígitos
    if (cedula.length === 10) {
      consultarDebounce(cedula);
    }
  });

  function showSkeleton() {
    ["nombre", "email", "whatsapp"].forEach((name) => {
      const input = document.querySelector(`[name="${name}"]`);
      input.classList.add("skeleton");
      input.value = "";
    });
  }

  function hideSkeleton() {
    ["nombre", "email", "whatsapp"].forEach((name) => {
      const input = document.querySelector(`[name="${name}"]`);
      input.classList.remove("skeleton");
    });
  }

  // =====================
  // INIT
  // =====================
  loadOrder();
  restoreUI();
  checkPayphoneCancel();

  // =====================
  // PAYPHONE CANCEL CHECK
  // =====================
  function checkPayphoneCancel() {
    const params = new URLSearchParams(window.location.search);
    const id = params.get("id");
    const clientTransactionId = params.get("clientTransactionId");

    if (id === "0" && clientTransactionId) {
      sessionStorage.removeItem("quickluck_order");
      Swal.fire({
        icon: "warning",
        title: "Pago cancelado",
        text: "El pago fue cancelado. Tu pedido no fue procesado.",
        confirmButtonColor: "#f5c518",
        background: "#111827",
        color: "#fff",
      }).then(() => {
        const url = new URL(window.location.href);
        url.search = "";
        window.location.href = url.toString();
      });
    }
  }

  // =====================
  // NAV CLICK
  // =====================
  document.querySelectorAll('[id^="nav-step-"]').forEach((el) => {
    el.addEventListener("click", () => {
      const step = parseInt(el.dataset.step);

      if (step === 2 && !ORDER.nombre) return;
      if (step === 3 && !ORDER.method) return;

      goToStep(step);
    });
  });

  // =====================
  // FORM INPUTS
  // =====================
  document.querySelectorAll("#form-datos input").forEach((input) => {
    input.addEventListener("input", () => {
      validarCampo(input);

      ORDER[input.name] = input.value;
      saveOrder();
    });
  });

  // =====================
  // QTY
  // =====================
  document.getElementById("qty-plus").onclick = () => {
    const maxAllowed = Math.min(PRODUCT.maxTickets, PRODUCT.availableTickets);
    if (ORDER.qty >= maxAllowed) {
      if (PRODUCT.availableTickets === 0) {
        swalAlertHome({
          icon: "warning",
          title: "¡Sin boletos disponibles!",
          text: "Lo sentimos, ya no hay boletos disponibles para este sorteo.",
        });
      } else {
        swalAlertHome({
          icon: "warning",
          title: "Límite alcanzado",
          text: `Solo puedes comprar máximo ${maxAllowed} boleto${maxAllowed > 1 ? "s" : ""} por transacción.`,
        });
      }
      return;
    }
    ORDER.qty++;
    qtyDisplay.textContent = ORDER.qty;
    updatePrice();
    saveOrder();
  };

  document.getElementById("qty-minus").onclick = () => {
    if (ORDER.qty > PRODUCT.minTickets) {
      ORDER.qty--;
      qtyDisplay.textContent = ORDER.qty;
      updatePrice();
      saveOrder();
    }
  };

  // =====================
  // FORM SUBMIT
  // =====================
  formDatos.onsubmit = async (e) => {
    e.preventDefault();

    let valido = true;

    document.querySelectorAll("#form-datos input").forEach((input) => {
      const ok = validarCampo(input);
      if (ok === undefined) valido = false;
    });

    if (!valido) return;

    // Check ticket availability before proceeding
    try {
      const res = await fetch(baseUrl + "api/tickets/disponibles");
      const data = await res.json();

      if (data.success) {
        PRODUCT.availableTickets = data.data.disponibles;
        PRODUCT.maxTickets = data.data.max_por_transaccion;

        if (PRODUCT.availableTickets === 0) {
          swalAlertHome({
            icon: "error",
            title: "¡Boletos Agotados!",
            text: "Lo sentimos, ya no hay boletos disponibles. Por favor intenta más tarde.",
          });
          return;
        }

        if (ORDER.qty > PRODUCT.availableTickets) {
          Swal.fire({
            icon: "warning",
            title: "Cantidad no disponible",
            text: `Solo hay ${PRODUCT.availableTickets} boleto(s) disponible(s). Se ajustará la cantidad.`,
            confirmButtonColor: "#f5c518",
            background: "#111827",
            color: "#fff",
          }).then(() => {
            ORDER.qty = Math.min(ORDER.qty, PRODUCT.availableTickets);
            qtyDisplay.textContent = ORDER.qty;
            updatePrice();
            saveOrder();
            goToStep(2);
          });
          return;
        }
      }
    } catch (err) {
      console.error("Error checking availability:", err);
    }

    goToStep(2);
  };

  // =====================
  // LOGICA DE BANCOS
  // =====================
  window.selectBank = (index) => {
    // 1. Limpiar estilos de todos los botones
    document.querySelectorAll(".bank-selector-btn").forEach((btn) => {
      btn.classList.remove("ring-2", "ring-brand-gold", "border-transparent");
      btn.classList.add("border-white/5");

      const img = btn.querySelector(".bank-logo-img");
      if (img) img.classList.add("grayscale");
    });

    // 2. Aplicar estilo al botón seleccionado
    const selectedBtn = document.querySelector(
      `.bank-selector-btn[data-bank-id="${index}"]`,
    );
    if (selectedBtn) {
      selectedBtn.classList.remove("border-white/5");
      selectedBtn.classList.add(
        "ring-2",
        "ring-brand-gold",
        "border-transparent",
      );

      const img = selectedBtn.querySelector(".bank-logo-img");
      if (img) img.classList.remove("grayscale");
    }

    // 3. Ocultar todos los paneles y el estado vacío
    document.querySelectorAll(".bank-detail-panel").forEach((panel) => {
      panel.classList.add("hidden");
    });
    document.getElementById("bank-empty-state").classList.add("hidden");

    // 4. Mostrar el panel correspondiente
    const selectedPanel = document.getElementById(`bank-detail-${index}`);
    if (selectedPanel) {
      selectedPanel.classList.remove("hidden");
    }
  };

  window.copyToClipboard = (text) => {
    navigator.clipboard
      .writeText(text)
      .then(() => {
        alert("¡Número de cuenta copiado al portapapeles!");
      })
      .catch((err) => {
        console.error("Error al copiar: ", err);
      });
  };

  // =====================
  // PAYMENT METHOD
  // =====================
  window.selectMethod = (method, el) => {
    ORDER.method = method;

    document
      .querySelectorAll(".payment-card")
      .forEach((c) => c.classList.remove("payment-card-selected"));

    el.classList.add("payment-card-selected");

    document
      .getElementById("bank-info")
      .classList.toggle("hidden", method !== "deposit");

    enableContinue();
    saveOrder();
  };

  // =====================
  // BUTTONS
  // =====================
  document.getElementById("btn-back-1").onclick = () => goToStep(1);
  document.getElementById("btn-back-2").onclick = () => goToStep(2);

  document.getElementById("btn-goto-3").onclick = () => {
    updateSummary();
    goToStep(3);
  };

  function swalAlertHome({ icon = "info", title, text }) {
    return Swal.fire({
      icon,
      title,
      text,
      confirmButtonColor: "#f5c518",
      background: "#111827",
      color: "#fff",
    });
  }

  // =====================
  // FINALIZAR
  // =====================
  document.getElementById("btn-finalizar").onclick = async () => {
    if (!document.getElementById("terms").checked) {
      swalAlertHome({
        icon: "warning",
        title: "Términos requeridos",
        text: "Debes aceptar los términos y condiciones para continuar.",
      });
      return;
    }
    const btn = document.getElementById("btn-finalizar");

    // PRELOADER
    btn.disabled = true;
    btn.innerHTML = "Procesando...";
    btn.classList.add("opacity-70");

    if (ORDER.method === "deposit") {
      showStep4Loading(); // ← agregar esto primero
      const res = await sendOrderToBackend();
      if (res && res.success) {
        clearOrder();
        showStep4Success();
        if (res.data) showTxnInfo(res.data);
      } else {
        goToStep(3); // ← volver al paso 3 si hay error
        btn.disabled = false;
        btn.innerHTML = "Confirmar →";
        btn.classList.remove("opacity-70");
        swalAlertHome({
          icon: "error",
          title: "Error del servidor",
          text:
            res?.message ||
            "No se pudo procesar la solicitud. Intenta de nuevo.",
        });
      }
    }

    if (ORDER.method === "card") {
      showStep4Loading();
      await iniciarPagoPayphone();
    }
  };

  // =====================
  // BACKEND
  // =====================
  async function sendOrderToBackend() {
    try {
      const res = await fetch(baseUrl + "api/orden/crear", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(ORDER),
      });

      const data = await res.json();

      return data;
    } catch (err) {
      console.error(err);
      return { success: false };
    }
  }

  async function iniciarPagoPayphone() {
    try {
      const res = await fetch(baseUrl + "payphone/pagar", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(ORDER),
      });

      const data = await res.json();

      if (data.success && data.url) {
        window.location.href = data.url;
      } else {
        goToStep(3);
        const btn = document.getElementById("btn-finalizar");
        btn.disabled = false;
        btn.innerHTML = "Confirmar →";
        btn.classList.remove("opacity-70");
        swalAlertHome({
          icon: "error",
          title: "Error",
          text: data.message || "No se pudo procesar el pago. Intenta de nuevo.",
        });
      }
    } catch (err) {
      console.error(err);
      goToStep(3);
      const btn = document.getElementById("btn-finalizar");
      btn.disabled = false;
      btn.innerHTML = "Confirmar →";
      btn.classList.remove("opacity-70");
      swalAlertHome({
        icon: "error",
        title: "Error de conexión",
        text: "No se pudo conectar con el servidor. Intenta de nuevo.",
      });
    }
  }
});
