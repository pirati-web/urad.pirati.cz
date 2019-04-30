// Add smooth scrolling
$('.scroll-link').on('click', function(e) {
  // Check for a hash value
  if (this.hash !== '') {
    // Prevent the default behavior
    e.preventDefault();

    // Store hash
    const hash = this.hash;

    // Animate smooth scroll - Wont work when using slim version of jQuery
    $('html, body').animate(
      { scrollTop: $(hash).offset().top },
      900,
      function() {
        // Add hash to URL after scroll
        window.location.hash = hash;
      }
    );
  }
});

$(function() {
  $('[data-toggle="tooltip"]').tooltip();
});
