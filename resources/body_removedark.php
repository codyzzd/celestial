<script>
  document.addEventListener('DOMContentLoaded', function () {
    function removeDarkClasses() {
      // Get all elements in the document
      var elements = document.querySelectorAll('*');

      elements.forEach(function (element) {
        // Get the current class list
        var classList = Array.from(element.classList);

        // Filter out any class that starts with 'dark:'
        var newClassList = classList.filter(function (cls) {
          // Remove any class that starts with 'dark:' or has a breakpoint prefix like 'md:dark:'
          return !cls.split(':').includes('dark');
        });

        // Set the new class list on the element
        element.className = newClassList.join(' ');
      });
    }

    // Call the function to remove dark classes
    removeDarkClasses();
  });
</script>