document.addEventListener("DOMContentLoaded", function () {
  function mostrarNotificacion(mensaje) {
    let notificacion = document.getElementById("siteToast");

    if (notificacion) {
      notificacion.querySelector(".toast-body").textContent = mensaje;

      let toast = new bootstrap.Toast(notificacion, {
        delay: 3000,
      });

      toast.show();
    }
  }

  let formularios = document.querySelectorAll(".demo-auth-form");

  formularios.forEach(function (formulario) {
    formulario.addEventListener("submit", function (evento) {
      evento.preventDefault();

      mostrarNotificacion("Sesión iniciada.");

      let modal = document.getElementById("loginModal");

      if (modal) {
        let ventana = bootstrap.Modal.getInstance(modal);

        if (ventana) {
          ventana.hide();
        }
      }
    });
  });

  let cuadrillaProductos = document.getElementById("productGrid");

  if (cuadrillaProductos) {
    let botonesFiltro = document.querySelectorAll(".filter-btn");
    let selectorOrden = document.getElementById("sortProducts");
    let filtroActivo = "todos";

    function actualizarProductos() {
      let productos = document.querySelectorAll(".product-item");

      productos.forEach(function (producto) {
        producto.style.order = 0;

        if (
          filtroActivo == "todos" ||
          producto.dataset.category == filtroActivo
        ) {
          producto.classList.remove("d-none");
        } else {
          producto.classList.add("d-none");
        }
      });

      let lista = Array.from(productos);

      lista.sort(function (a, b) {
        let precioA = Number(a.dataset.price);
        let precioB = Number(b.dataset.price);

        if (selectorOrden && selectorOrden.value == "low") {
          return precioA - precioB;
        }

        if (selectorOrden && selectorOrden.value == "high") {
          return precioB - precioA;
        }

        return 0;
      });

      lista.forEach(function (producto) {
        cuadrillaProductos.appendChild(producto);
      });

      let hayProductos = false;

      lista.forEach(function (producto) {
        if (!producto.classList.contains("d-none")) {
          hayProductos = true;
        }
      });

      let mensajeVacio = document.getElementById("emptyProducts");

      if (mensajeVacio) {
        mensajeVacio.classList.toggle("d-none", hayProductos);
      }
    }

    botonesFiltro.forEach(function (boton) {
      boton.addEventListener("click", function () {
        filtroActivo = boton.dataset.filter;

        botonesFiltro.forEach(function (elemento) {
          elemento.classList.remove("active");
        });

        boton.classList.add("active");

        actualizarProductos();
      });
    });

    if (selectorOrden) {
      selectorOrden.addEventListener("change", actualizarProductos);
    }

    let carrito = [];

    let elementosCarrito = document.getElementById("cartItems");
    let contadorCarrito = document.getElementById("cartCount");
    let totalCarrito = document.getElementById("cartTotal");
    let botonCompra = document.getElementById("checkoutBtn");

    function mostrarCarrito() {
      let cantidadTotal = 0;
      let precioTotal = 0;

      carrito.forEach(function (elemento) {
        cantidadTotal += elemento.cantidad;
        precioTotal += elemento.precio * elemento.cantidad;
      });

      contadorCarrito.textContent = cantidadTotal;

      totalCarrito.textContent = precioTotal.toLocaleString("es-ES", {
        style: "currency",
        currency: "EUR",
      });

      botonCompra.disabled = cantidadTotal == 0;

      elementosCarrito.innerHTML = "";

      carrito.forEach(function (elemento, indice) {
        elementosCarrito.innerHTML += `
                    <div class="cart-line">
                        <div>
                            <strong>${elemento.nombre}</strong>
                            <small>Cantidad: ${elemento.cantidad}</small>
                        </div>

                        <div class="text-end">
                            <strong>${(elemento.precio * elemento.cantidad).toFixed(2)} €</strong>

                            <button class="cart-remove d-block ms-auto" data-remove="${indice}">
                                <i class="bi bi-trash"></i> Quitar
                            </button>
                        </div>
                    </div>
                `;
      });

      let botonesEliminar = elementosCarrito.querySelectorAll("[data-remove]");

      botonesEliminar.forEach(function (boton) {
        boton.addEventListener("click", function () {
          let indice = Number(boton.dataset.remove);

          carrito.splice(indice, 1);

          mostrarCarrito();
        });
      });
    }

    let botonesCarrito = document.querySelectorAll(".add-cart-btn");

    botonesCarrito.forEach(function (boton) {
      boton.addEventListener("click", function () {
        let nombre = boton.dataset.name;
        let precio = Number(boton.dataset.price);
        let encontrado = false;

        carrito.forEach(function (elemento) {
          if (elemento.nombre == nombre) {
            elemento.cantidad++;
            encontrado = true;
          }
        });

        if (!encontrado) {
          carrito.push({
            nombre: nombre,
            precio: precio,
            cantidad: 1,
          });
        }

        mostrarCarrito();

        mostrarNotificacion(nombre + " añadido al carrito.");
      });
    });

    let botonesFavoritos = document.querySelectorAll(".wishlist-btn");

    botonesFavoritos.forEach(function (boton) {
      boton.addEventListener("click", function () {
        boton.classList.toggle("is-favorite");

        if (boton.classList.contains("is-favorite")) {
          boton.innerHTML = '<i class="bi bi-heart-fill"></i>';
        } else {
          boton.innerHTML = '<i class="bi bi-heart"></i>';
        }
      });
    });
  }

  let formularioContacto = document.getElementById("contactForm");

  if (formularioContacto) {
    formularioContacto.addEventListener("submit", function (evento) {
      evento.preventDefault();

      if (!formularioContacto.checkValidity()) {
        formularioContacto.reportValidity();
        return;
      }

      document.getElementById("contactSuccess").classList.remove("d-none");

      formularioContacto.reset();
    });
  }
});
