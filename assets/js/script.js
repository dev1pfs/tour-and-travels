document.addEventListener("DOMContentLoaded", function () {

    // navbar active class js 
    const currentPage = window.location.pathname;
    const navLinks = document.querySelectorAll(".navbar .nav-link");
    navLinks.forEach(link => link.classList.remove("active"));
    let matchFound = false;
    navLinks.forEach(link => {
        if (currentPage.endsWith(link.getAttribute("href"))) {
            link.classList.add("active");
            matchFound = true;
        }
    });
    if (!matchFound && navLinks.length > 0) {
        navLinks[0].classList.add("active");
    }
    navLinks.forEach(link => {
        link.addEventListener("click", function () {
            navLinks.forEach(l => l.classList.remove("active"));
            this.classList.add("active");
        });
    });



    // mobile toggle menu js
    const toggleMenu = document.querySelector(".toggle-menu");
    const navbar = document.querySelector(".navbar-wrapper");
    const navLinksToggle = document.querySelectorAll(".navbar-wrapper ul li a");
    toggleMenu.addEventListener("click", (e) => {
        e.stopPropagation();
        navbar.classList.toggle("active");
    });
    document.addEventListener("click", (e) => {
        if (!navbar.contains(e.target) && navbar.classList.contains("active")) {
            navbar.classList.remove("active");
        }
    });
    navLinksToggle.forEach(link => {
        link.addEventListener("click", () => {
            navbar.classList.remove("active");
        });
    });






    // banner swiper js 
    var banner = new Swiper(".swiperbanner", {
        loop: true,
        slidesPerView: 1,
        speed: 2000,
        autoplay: {
            delay: 2000,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });


    // mouse cursor js 
    const cursor = document.querySelector('.cursor');
    document.addEventListener('mousemove', e => {
        cursor.style.top = e.clientY + "px";
        cursor.style.left = e.clientX + "px";
    });
    document.addEventListener('click', () => {
        cursor.classList.add("expand");
        setTimeout(() => {
            cursor.classList.remove("expand");
        }, 500);
    });


    // scroll top btn js 
    const scrollToTop = document.getElementById('scroll-top');
    window.addEventListener('scroll', function () {
        if (window.scrollY > 200) {
            document.body.classList.add('scroll-visiable')
        } else {
            document.body.classList.remove('scroll-visiable')
        }
    });
    scrollToTop.addEventListener('click', function (e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });



    // search form 
    const searchIcon = document.getElementById('search-icon');
    const searchForm = document.getElementById('search-form');
    const closeBtn = document.getElementById('closebtn');

    searchIcon.addEventListener('click', (e) => {
        e.preventDefault();
        searchForm.classList.add('open');
        e.stopPropagation();
    });

    closeBtn.addEventListener('click', (e) => {
        e.preventDefault();
        searchForm.classList.remove('open');
        e.stopPropagation();
    });

    searchForm.addEventListener('click', (e) => {
        e.stopPropagation();
    })

    document.addEventListener('click', () => {
        searchForm.classList.remove('open');
    });




});


// password 
document.addEventListener("DOMContentLoaded", function () {
    const password = document.getElementById('password');
    const toggle = document.getElementById('togglePassword');
    const eyeOpen = document.getElementById('eye-open');
    const eyeClose = document.getElementById('eye-closed');

    if (toggle) {
        console.log(toggle);
        toggle.addEventListener('click', () => {
            if (password.type === "password") {
                password.type = "text";
                eyeClose.style.display = "none";
                eyeOpen.style.display = "block"
            } else {
                password.type = "password";
                eyeClose.style.display = "block";
                eyeOpen.style.display = "none";
            }
        });
    }
});




// sidebar menu js 
document.addEventListener("DOMContentLoaded", function () {
    const topLinks = document.querySelectorAll('.sidebar-menu > li > a.has-submenu');
    topLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            const li = link.parentElement;
            const submenu = li.querySelector('.submenu');
            const isActive = li.classList.contains('active');

            document.querySelectorAll('.sidebar-menu > li').forEach(otherLi => {
                if (otherLi !== li) {
                    otherLi.classList.remove('active');
                    const otherSub = otherLi.querySelector('.submenu');
                    if (otherSub) otherSub.style.maxHeight = null;
                }
            });

            if (isActive) {
                li.classList.remove('active');
                if (submenu) submenu.style.maxHeight = null;
            } else {
                li.classList.add('active');
                if (submenu) submenu.style.maxHeight = submenu.scrollHeight + "px";
            }
        });
    });
});


// window reload js 
// document.addEventListener("DOMContentLoaded", function () {
//     window.addEventListener("load", () => {
//         const preloader = document.getElementById("preloader");
//         if (preloader) {
//             setTimeout(() => {
//                 preloader.classList.add("hidden");
//                 document.querySelector(".content").style.display = "block";
//             }, 2000);
//         }
//     });
// });

document.addEventListener("DOMContentLoaded", function () {
    window.addEventListener("load", () => {
        const preloader = document.getElementById("preloader");
        const content = document.querySelector(".content");

        if (preloader) {
            setTimeout(() => {
                preloader.classList.add("hidden");

                if (content) {
                    content.style.display = "block";
                }
            }, 2000);
        }
    });
});





