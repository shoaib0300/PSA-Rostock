document.querySelector('.custom-prev')?.addEventListener('click', () => {
    $('.main-slider').slick('slickPrev');
});

document.querySelector('.custom-next')?.addEventListener('click', () => {
    $('.main-slider').slick('slickNext');
});

function initializeSlider() {
    $('.main-slider').slick({
        lazyLoad: 'ondemand',
        slidesToShow: 1,
        slidesToScroll: 1,
        centerMode: true,
        centerPadding: '20%',
        infinite: true,
        arrows: true,
        dots: true,
        focusOnSelect: true,
        responsive: [
            {
                breakpoint: 768,
                settings: {
                    centerPadding: '10%',
                    slidesToShow: 1,
                    arrows: false
                }
            }
        ]
    });
}

document.addEventListener('DOMContentLoaded', initializeSlider);


document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const closeModal = document.getElementById('closeModal');
    const prevImage = document.getElementById('prevImage');
    const nextImage = document.getElementById('nextImage');
    const images = Array.from(document.querySelectorAll('.slider-image'));
    let currentIndex = -1;

    // Open modal on image click
    images.forEach((image, index) => {
        image.addEventListener('click', (e) => {
            currentIndex = index;
            const imageSrc = e.target.getAttribute('data-src'); // Get image path
            modalImage.src = imageSrc; // Set modal image
            modal.style.display = 'flex'; // Show modal
        });
    });

    // Close modal
    closeModal.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    // Close modal when clicking outside the image
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Navigate to the previous image
    prevImage.addEventListener('click', () => {
        if (currentIndex > 0) {
            currentIndex--;
        } else {
            currentIndex = images.length - 1; // Wrap to the last image
        }
        modalImage.src = images[currentIndex].getAttribute('data-src');
    });

    // Navigate to the next image
    nextImage.addEventListener('click', () => {
        if (currentIndex < images.length - 1) {
            currentIndex++;
        } else {
            currentIndex = 0; // Wrap to the first image
        }
        modalImage.src = images[currentIndex].getAttribute('data-src');
    });

    // Optional: Use keyboard arrows for navigation
    // document.addEventListener('keydown', (e) => {
    //     if (modal.style.display === 'flex') {
    //         if (e.key === 'ArrowLeft') {
    //             prevImage.click();
    //         } else if (e.key === 'ArrowRight') {
    //             nextImage.click();
    //         } else if (e.key === 'Escape') {
    //             closeModal.click();
    //         }
    //     }
    // });
});