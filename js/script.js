document.addEventListener("DOMContentLoaded", function () {
    var form = document.getElementById("formProducto");
    var btnGuardar = document.getElementById("btnGuardar");
    var selBodega = document.getElementById("bodega");
    var selSucursal = document.getElementById("sucursal");
    var selMoneda = document.getElementById("moneda");

    cargarOpciones(selBodega, "api/bodegas.php");
    cargarOpciones(selMoneda, "api/monedas.php");

    selBodega.addEventListener("change", function () {
        limpiarSelect(selSucursal, "Seleccionar sucursal");
        selSucursal.disabled = true;

        var idBodega = selBodega.value;
        if (!idBodega) {
            return;
        }

        selSucursal.disabled = false;
        cargarOpciones(selSucursal, "api/sucursales.php?id_bodega=" + encodeURIComponent(idBodega), "Seleccionar sucursal");
    });

    form.addEventListener("submit", function (evento) {
        evento.preventDefault();

        if (!validarFormulario()) {
            return;
        }

        var datos = {
            codigo: document.getElementById("codigo").value.trim(),
            nombre: document.getElementById("nombre").value.trim(),
            bodega: selBodega.value,
            sucursal: selSucursal.value,
            moneda: selMoneda.value,
            precio: document.getElementById("precio").value.trim(),
            materiales: obtenerMateriales(),
            descripcion: document.getElementById("descripcion").value
        };

        verificarCodigoUnico(datos.codigo)
            .then(function (codigoExiste) {
                if (codigoExiste) {
                    alert("El código del producto ya está registrado.");
                    return;
                }
                guardarProducto(datos);
            })
            .catch(function () {
                alert("No se pudo verificar el código del producto. Intente nuevamente.");
            });
    });

    function cargarOpciones(select, url, textoVacio, mantenerSeleccion) {
        fetch(url)
            .then(function (respuesta) {
                if (!respuesta.ok) {
                    throw new Error("Error en la respuesta del servidor");
                }
                return respuesta.json();
            })
            .then(function (datos) {
                var seleccion = mantenerSeleccion ? select.value : "";
                limpiarSelect(select, textoVacio || "Seleccionar opción");

                datos.forEach(function (item) {
                    var opcion = document.createElement("option");
                    opcion.value = item.id;
                    opcion.textContent = item.nombre;
                    select.appendChild(opcion);
                });

                if (mantenerSeleccion) {
                    select.value = seleccion;
                }
            })
            .catch(function () {
                alert("No se pudieron cargar las opciones desde el servidor.");
            });
    }

    function limpiarSelect(select, textoVacio) {
        select.innerHTML = "";
        var opcionVacia = document.createElement("option");
        opcionVacia.value = "";
        opcionVacia.textContent = textoVacio;
        select.appendChild(opcionVacia);
        select.value = "";
    }

    function obtenerMateriales() {
        return Array.prototype.slice
            .call(document.querySelectorAll('input[name="materiales[]"]:checked'))
            .map(function (checkbox) {
                return checkbox.value;
            });
    }

    function validarFormulario() {
        var codigo = document.getElementById("codigo").value.trim();
        var nombre = document.getElementById("nombre").value.trim();
        var precio = document.getElementById("precio").value.trim();
        var materiales = obtenerMateriales();
        var descripcion = document.getElementById("descripcion").value;

        var regexCodigo = /^(?=.*[a-zA-Z])(?=.*[0-9])[a-zA-Z0-9]+$/;
        var regexPrecio = /^\d+(\.\d{1,2})?$/;

        if (codigo === "") {
            alert("El código del producto no puede estar en blanco.");
            return false;
        }

        if (!regexCodigo.test(codigo)) {
            alert("El código del producto debe contener letras y números");
            return false;
        }

        if (codigo.length < 5 || codigo.length > 15) {
            alert("El código del producto debe tener entre 5 y 15 caracteres.");
            return false;
        }

        if (nombre === "") {
            alert("El nombre del producto no puede estar en blanco.");
            return false;
        }

        if (nombre.length < 2 || nombre.length > 50) {
            alert("El nombre del producto debe tener entre 2 y 50 caracteres.");
            return false;
        }

        if (precio === "") {
            alert("El precio del producto no puede estar en blanco.");
            return false;
        }

        if (!regexPrecio.test(precio)) {
            alert("El precio del producto debe ser un número positivo con hasta dos decimales.");
            return false;
        }

        if (materiales.length < 2) {
            alert("Debe seleccionar al menos dos materiales para el producto.");
            return false;
        }

        if (selBodega.value === "") {
            alert("Debe seleccionar una bodega.");
            return false;
        }

        if (selSucursal.value === "") {
            alert("Debe seleccionar una sucursal para la bodega seleccionada.");
            return false;
        }

        if (selMoneda.value === "") {
            alert("Debe seleccionar una moneda para el producto.");
            return false;
        }

        if (descripcion === "") {
            alert("La descripción del producto no puede estar en blanco.");
            return false;
        }

        if (descripcion.length < 10 || descripcion.length > 1000) {
            alert("La descripción del producto debe tener entre 10 y 1000 caracteres.");
            return false;
        }

        return true;
    }

    function verificarCodigoUnico(codigo) {
        return fetch("api/verificar_codigo.php?codigo=" + encodeURIComponent(codigo))
            .then(function (respuesta) {
                if (!respuesta.ok) {
                    throw new Error("Error en la respuesta del servidor");
                }
                return respuesta.json();
            })
            .then(function (resultado) {
                return resultado.existe === true || resultado.existe === 1;
            });
    }

    function guardarProducto(datos) {
        btnGuardar.disabled = true;

        fetch("api/guardar_producto.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(datos)
        })
            .then(function (respuesta) {
                if (!respuesta.ok) {
                    throw new Error("Error en la respuesta del servidor");
                }
                return respuesta.json();
            })
            .then(function (resultado) {
                alert(resultado.mensaje || (resultado.exito ? "Producto guardado correctamente." : "No se pudo guardar el producto."));
                if (resultado.exito) {
                    form.reset();
                    limpiarSelect(selSucursal, "Seleccionar sucursal");
                    selSucursal.disabled = true;
                }
            })
            .catch(function () {
                alert("No se pudo conectar con el servidor. Intente nuevamente.");
            })
            .finally(function () {
                btnGuardar.disabled = false;
            });
    }
});
