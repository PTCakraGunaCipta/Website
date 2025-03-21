document.addEventListener("DOMContentLoaded", function () {
  const menuBar = document.querySelector('.menu-bar');
  const navContainer = document.querySelector('.nav-container2');

  menuBar.addEventListener('click', () => {
      if (navContainer.style.right === "0px") {
          navContainer.style.right = "-250px";
      } else {
          navContainer.style.right = "0px";
      }
  });

  // Tutup menu saat klik di luar sidebar
  document.addEventListener("click", function (event) {
      if (!menuBar.contains(event.target) && !navContainer.contains(event.target)) {
          navContainer.style.right = "-250px";
      }
  });
});

