document.addEventListener("DOMContentLoaded", function () {
  const editarModal = document.getElementById("editarModal");
  editarModal.addEventListener("show.bs.modal", function (event) {
    const button = event.relatedTarget;

    const id = button.getAttribute("data-id");
    const placa = button.getAttribute("data-placa");
    const tipo_vehiculo = button.getAttribute("data-tipo_vehiculo");
    const capacidad = button.getAttribute("data-capacidad");
    const servicio = button.getAttribute("data-servicio");
    const empresaId = button.getAttribute("data-empresa-id");

    document.getElementById("id_vehiculo_editar").value = id;
    document.getElementById("placa_editar").value = placa;
    document.getElementById("tipo_vehiculo_editar").value = tipo_vehiculo;
    document.getElementById("capacidad_editar").value = capacidad;

    document.getElementById("id_empresa_editar").value = empresaId;
    document.getElementById("servicio_editar").value = servicio;

    editarModal.querySelector(".modal-header").classList.remove("bg-red");
    editarModal.querySelector(".modal-header").classList.add("bg-primary");
  });
});
