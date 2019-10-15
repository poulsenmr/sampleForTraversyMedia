$( document ).ready(function() {
    // Mobile Nav Button Function
    let navButton = $(".Header-Hamburger")
    let mobileLinks = $(".Header-Mobile-Links")
    navButton.click(function() {
        mobileLinks.slideToggle("slow");
    });

    // Top-Banner Slide Function
    // Test to see if the banner is present.
    let classExist = $('.mySlides');
    if(classExist[0]) {
        var slideIndex = 4;
        showDivs(slideIndex);
        carousel();
        $('.Left-Button').click(function () {
            plusDivs(-1);
        });
        $('.Right-Button').click(function () {
            plusDivs(1);
        });
        $('.Button-Slide-First').click(function () {
            currentDiv(1)
        });
        $('.Button-Slide-Second').click(function () {
            currentDiv(2)
        });
        $('.Button-Slide-Third').click(function () {
            currentDiv(3)
        });
        $('.Button-Slide-Forth').click(function () {
            currentDiv(4)
        });

        function plusDivs(n) {
            showDivs(slideIndex += n);
        }

        function currentDiv(n) {
            showDivs(slideIndex = n);
        }

        function carousel() {
            plusDivs(1);
            setTimeout(carousel, 10000); // Change image every 10 seconds
        }

        function showDivs(n) {
            let i;
            let x = $('.mySlides');
            let b = $('.mySlidesSelect');
            if (n > x.length) {
                slideIndex = 1
            }
            if (n < 1) {
                slideIndex = x.length
            }
            for (i = 0; i < x.length; i++) {
                x[i].style.display = "none";
                b[i].style.backgroundColor = "transparent";
            }
            x[slideIndex - 1].style.display = "block";
            b[slideIndex - 1].style.backgroundColor = "darkgreen";
        }
    }
});
