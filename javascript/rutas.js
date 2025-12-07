var modalReserva = document.getElementById("modalReserva");

modalReserva.addEventListener("show.bs.modal", function (event) {
  var button = event.relatedTarget;

  var idRuta = button.getAttribute("data-idruta");
  var capacidad = button.getAttribute("data-capacidad");
  var ruta = button.getAttribute("data-ruta");
  var fecha = button.getAttribute("data-fecha");

  var idRutaInput = modalReserva.querySelector("#idRutaInput");
  var capacidadInput = modalReserva.querySelector("#cantidadAsientos");
  var capacidadInfo = modalReserva.querySelector("#capacidadInfo");
  var rutaInfo = modalReserva.querySelector("#rutaInfo");
  var fechaInfo = modalReserva.querySelector("#fechaInfo");

  idRutaInput.value = idRuta;
  rutaInfo.textContent = ruta;
  fechaInfo.textContent = fecha;
  capacidadInfo.textContent = capacidad;

  capacidadInput.setAttribute("max", capacidad);
});
