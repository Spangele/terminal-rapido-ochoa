document.addEventListener("DOMContentLoaded", function () {
  const editarModal = document.getElementById("editarModal");
  editarModal.addEventListener("show.bs.modal", function (event) {
    const button = event.relatedTarget;

    const id = button.getAttribute("data-id");
    const vehiculoId = button.getAttribute("data-vehiculo-id");
    const origen = button.getAttribute("data-origen");
    const destino = button.getAttribute("data-destino");
    const precio = parseFloat(button.getAttribute("data-precio")).toFixed(2);
    const fecha = button.getAttribute("data-fecha");
    const hora = button.getAttribute("data-hora");

    document.getElementById("id_ruta_editar").value = id;
    document.getElementById("id_vehiculo_editar").value = vehiculoId;
    document.getElementById("origen_editar").value = origen;
    document.getElementById("destino_editar").value = destino;
    document.getElementById("precio_editar").value = precio;
    document.getElementById("fecha_editar").value = fecha;
    document.getElementById("hora_editar").value = hora;

    editarModal.querySelector(".modal-header").classList.remove("bg-red");
    editarModal.querySelector(".modal-header").classList.add("bg-primary");
  });
});
