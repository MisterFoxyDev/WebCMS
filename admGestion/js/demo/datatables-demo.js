// Call the dataTables jQuery plugin
$(document).ready(function () {
  $("#dataTable").DataTable({
    language: {
      emptyTable: "Aucune donnée disponible dans le tableau",
      info: "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
      infoEmpty: "Affichage de 0 à 0 sur 0 entrées",
      infoFiltered: "(filtré à partir de _MAX_ entrées au total)",
      infoPostFix: "",
      thousands: ",",
      lengthMenu: "Afficher _MENU_ entrées",
      loadingRecords: "Chargement...",
      processing: "Traitement...",
      search: "Rechercher:",
      zeroRecords: "Aucun enregistrement correspondant trouvé",
      paginate: {
        first: "Premier",
        last: "Dernier",
        next: "Suivant",
        previous: "Précédent",
      },
      aria: {
        sortAscending: ": activer pour trier la colonne par ordre croissant",
        sortDescending: ": activer pour trier la colonne par ordre décroissant",
      },
    },
  });
});
