// Hosteller script.js

console.log("Hosteller script loaded");
document.addEventListener("DOMContentLoaded", function () {
    // === Navigation click logic ===
    const pageLinks = {
    "home": "wHome.php",
    "notification": "wNotification.php",
    "manage": "wManage.php",
    "checkout": "wCheckout.php",
    "payments": "wPayment.php",
    "reports": "wReport.php",
    "settings": "settings.html",
    "view-checkout-btn": "wCheckout.php",
    "view-report-btn": "wReport.php"
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

    // Fade-in on page load
    document.body.style.transition = "opacity 1.5s";
    document.body.style.opacity = "1";

    // === Highlight Current Page in Nav ===
    const currentPage = window.location.pathname.split("/").pop(); // gets the current filename
    for (let className in pageLinks) {
        if (pageLinks[className] === currentPage) {
            const activeLink = document.querySelector(`.left-nav ul li.${className}`);
            if (activeLink) {
                activeLink.classList.add("active");
            }
            break;
        }
    }

    // === Logout button logic ===
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

     // === Tooltip User Logic ===
  const tooltipUserDiv = document.getElementById("tooltip-user");

  if (tooltipUserDiv) {
      fetch("getUserInfo.php")
          .then(response => response.json())
          .then(data => {
              if (data.name && data.role) {
                  const tooltipText = `${data.name} (${data.role})`;

                  // Create tooltip element
                  const tooltip = document.createElement('div');
                  tooltip.className = 'custom-tooltip';
                  tooltip.textContent = tooltipText;
                  document.body.appendChild(tooltip);

                  // Only show tooltip when hovering the #tooltip-user div
                  tooltipUserDiv.addEventListener('mouseenter', function (e) {
                    tooltip.style.opacity = '1';
                    tooltip.style.transform = 'translateX(0)';
                    tooltip.style.left = (e.pageX - tooltip.offsetWidth - 10) + 'px';
                    tooltip.style.top = (e.pageY + 10) + 'px';
                    });
                    
                    tooltipUserDiv.addEventListener('mousemove', function (e) {
                        tooltip.style.left = (e.pageX - tooltip.offsetWidth - 10) + 'px';
                        tooltip.style.top = (e.pageY + 10) + 'px';
                    });
                    
                    tooltipUserDiv.addEventListener('mouseleave', function () {
                        tooltip.style.opacity = '0';
                        tooltip.style.transform = 'translateX(-10px)';
                    });
              } else {
                  console.warn("User data missing in response");
              }
          })
          .catch(error => {
              console.error("Error fetching user info:", error);
          });
  } else {
      console.warn("Tooltip user div not found");
  }
});