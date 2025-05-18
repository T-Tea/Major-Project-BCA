//admin script.js
document.addEventListener("DOMContentLoaded", function () {
    // === Page Navigation Links ===
    const pageLinks = {
      "home": "adHome.php",
      "manage":"adManage.php",
      "notification": "adNotification.php",
      "status": "adStatus.php",
      "payments": "adPayment.php",
      "reports": "adReport.php",
      "settings": "adSettings.html"
    };
  
    function handleNavigationClick(event) {
      for (let className in pageLinks) {
        if (event.target.closest("li").classList.contains(className)) {
          document.body.style.opacity = "0";
          setTimeout(() => {
            window.location.href = pageLinks[className];
          }, 300);
          break;
        }
      }
    }
  
    const navList = document.querySelector(".left-nav ul");
    if (navList) {
      navList.addEventListener("click", handleNavigationClick);
    }
  
    // === Page Fade In ===
    document.body.style.transition = "opacity 1.5s";
    document.body.style.opacity = "1";
  
    // === Logout Button Logic ===
    const logoutBtn = document.getElementById("logout-btn");
    if (logoutBtn) {
      logoutBtn.addEventListener("click", function () {
        const confirmLogout = confirm("Are you sure you want to log out?");
        if (confirmLogout) {
          window.location.href = "../logic/logout.php";
        }
      });
    } else {
      console.warn("Logout button not found");
    }
  });
  
  