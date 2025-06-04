// DOM yüklendiğinde çalışacak fonksiyon
document.addEventListener('DOMContentLoaded', function() {
    initializeAccordion();
    initializeMobileMenu();
    initializeDropdown();
});

// Akordeon fonksiyonalitesi
function initializeAccordion() {
    const accordionButtons = document.querySelectorAll('.accordion-button');

    accordionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const accordionItem = this.closest('.accordion-item');
            const accordionContent = accordionItem.querySelector('.accordion-content');
            const accordionIcon = this.querySelector('.accordion-icon');
            const isActive = accordionItem.classList.contains('active');

            // Tüm accordion itemleri kapat
            document.querySelectorAll('.accordion-item').forEach(item => {
                item.classList.remove('active');
                const content = item.querySelector('.accordion-content');
                const icon = item.querySelector('.accordion-icon');
                const button = item.querySelector('.accordion-button');

                content.style.maxHeight = '0';
                icon.textContent = '+';
                button.setAttribute('aria-expanded', 'false');
            });

            // Eğer tıklanan item aktif değilse, aç
            if (!isActive) {
                accordionItem.classList.add('active');
                accordionContent.style.maxHeight = accordionContent.scrollHeight + 'px';
                accordionIcon.textContent = '−';
                this.setAttribute('aria-expanded', 'true');
            }
        });
    });
}

// Menü kapatma helper fonksiyonu - DRY principle
function closeAllMenus() {
    const navLinks = document.querySelector('.nav-links');
    const mobileToggle = document.querySelector('.mobile-toggle');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    const dropdownArrow = document.querySelector('.dropdown-arrow');
    const arrow = document.querySelector('.mobile-arrow');

    if (navLinks && mobileToggle) {
        navLinks.classList.remove('active');
        mobileToggle.classList.remove('active');
        mobileToggle.setAttribute('aria-expanded', 'false');
    }

    if (dropdownMenu && dropdownArrow) {
        dropdownMenu.classList.remove('show');
        dropdownArrow.setAttribute('aria-expanded', 'false');
        if (arrow) arrow.style.transform = 'rotate(0deg)';
    }

    document.body.style.overflow = '';
}

// Mobil menü fonksiyonalitesi
function initializeMobileMenu() {
    const mobileToggle = document.querySelector('.mobile-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (mobileToggle && navLinks) {
        mobileToggle.addEventListener('click', function() {
            const isActive = navLinks.classList.contains('active');

            // Menü durumunu toggle et
            navLinks.classList.toggle('active');
            mobileToggle.classList.toggle('active');

            // Accessibility için aria-expanded güncelle
            const expanded = !isActive;
            mobileToggle.setAttribute('aria-expanded', expanded);

            // Body scroll'unu kontrol et (mobil menü açıkken)
            document.body.style.overflow = expanded ? 'hidden' : '';
        });

        // Menü linklerine tıklandığında menüyü kapat
        const menuLinks = navLinks.querySelectorAll('a');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                closeAllMenus();
            });
        });
    }
}

// Dropdown menü fonksiyonalitesi
function initializeDropdown() {
    const dropdownArrow = document.querySelector('.dropdown-arrow');
    const dropdown = document.querySelector('.dropdown');

    if (dropdownArrow && dropdown) {
        // Ok butonuna tıklandığında dropdown aç/kapat
        dropdownArrow.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const dropdownMenu = dropdown.querySelector('.dropdown-menu');
            const arrow = this.querySelector('.mobile-arrow');
            const isVisible = dropdownMenu.classList.contains('show');

            // Dropdown'u toggle et
            if (isVisible) {
                dropdownMenu.classList.remove('show');
                this.setAttribute('aria-expanded', 'false');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            } else {
                dropdownMenu.classList.add('show');
                this.setAttribute('aria-expanded', 'true');
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            }
        });

        // Dropdown dışına tıklandığında kapat
        document.addEventListener('click', function(e) {
            if (!dropdownArrow.contains(e.target) && !dropdown.querySelector('.dropdown-menu').contains(e.target)) {
                const dropdownMenu = dropdown.querySelector('.dropdown-menu');
                const arrow = dropdownArrow.querySelector('.mobile-arrow');
                dropdownMenu.classList.remove('show');
                dropdownArrow.setAttribute('aria-expanded', 'false');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        });
    }
}

// Sayfa resize olduğunda menüleri kapat
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        closeAllMenus();
    }
});
