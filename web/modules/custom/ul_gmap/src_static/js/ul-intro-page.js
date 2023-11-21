export function introAnimations() {
    
    var iconOverlay = document.querySelector('.ul-intro-icons-overlay'),
        superTitle = document.querySelector('.ul-intro-supertitle'),
        description = document.querySelector('.ul-intro-description'),
        buttons = document.querySelector('.ul-intro-buttons');

        iconOverlay.classList.add('ul-intro-active-icons');

        superTitle.classList.add('animate__fadeInUp');
        description.classList.add('animate__fadeInUp');
        buttons.classList.add('animate__fadeInUp');

}