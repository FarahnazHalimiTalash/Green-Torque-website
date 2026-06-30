/**
 * Green Torque Auto Repair — Main Script
 */

(function () {
  'use strict';

  const header = document.getElementById('header');
  const navToggle = document.getElementById('nav-toggle');
  const navMenu = document.getElementById('nav-menu');
  const navLinks = document.querySelectorAll('.nav__link');
  const bookingForm = document.getElementById('booking-form');
  const formStatus = document.getElementById('form-status');
  const dateInput = document.getElementById('date');
  const yearEl = document.getElementById('year');

  /* Footer year */
  if (yearEl) {
    yearEl.textContent = new Date().getFullYear();
  }

  /* Minimum booking date = tomorrow */
  if (dateInput) {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    dateInput.min = tomorrow.toISOString().split('T')[0];
  }

  /* Sticky header shadow */
  function onScroll() {
    if (window.scrollY > 20) {
      header.classList.add('header--scrolled');
    } else {
      header.classList.remove('header--scrolled');
    }
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* Mobile navigation */
  function closeMenu() {
    navToggle.classList.remove('nav__toggle--open');
    navMenu.classList.remove('nav__menu--open');
    navToggle.setAttribute('aria-expanded', 'false');
    navToggle.setAttribute('aria-label', 'Open menu');
  }

  function openMenu() {
    navToggle.classList.add('nav__toggle--open');
    navMenu.classList.add('nav__menu--open');
    navToggle.setAttribute('aria-expanded', 'true');
    navToggle.setAttribute('aria-label', 'Close menu');
  }

  navToggle.addEventListener('click', function () {
    if (navMenu.classList.contains('nav__menu--open')) {
      closeMenu();
    } else {
      openMenu();
    }
  });

  navLinks.forEach(function (link) {
    link.addEventListener('click', function () {
      closeMenu();
    });
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeMenu();
  });

  /* Active nav link on scroll */
  const sections = document.querySelectorAll('section[id]');

  function setActiveLink() {
    const scrollPos = window.scrollY + 100;

    sections.forEach(function (section) {
      const top = section.offsetTop;
      const height = section.offsetHeight;
      const id = section.getAttribute('id');

      if (scrollPos >= top && scrollPos < top + height) {
        navLinks.forEach(function (link) {
          link.classList.remove('nav__link--active');
          if (link.getAttribute('href') === '#' + id) {
            link.classList.add('nav__link--active');
          }
        });
      }
    });
  }

  window.addEventListener('scroll', setActiveLink, { passive: true });
  setActiveLink();

  /* Booking form */
  function showStatus(message, type) {
    formStatus.textContent = message;
    formStatus.className = 'form__status form__status--' + type;
  }

  function clearErrors() {
    bookingForm.querySelectorAll('.form__input--error').forEach(function (el) {
      el.classList.remove('form__input--error');
    });
  }

  function validateField(input) {
    if (!input.checkValidity()) {
      input.classList.add('form__input--error');
      return false;
    }
    input.classList.remove('form__input--error');
    return true;
  }

  if (bookingForm) {
    bookingForm.addEventListener('submit', function (e) {
      e.preventDefault();
      clearErrors();
      showStatus('', '');

      const fields = bookingForm.querySelectorAll('[required]');
      let valid = true;

      fields.forEach(function (field) {
        if (!validateField(field)) valid = false;
      });

      if (!valid) {
        showStatus('Please fill in all required fields correctly.', 'error');
        return;
      }

      const formData = new FormData(bookingForm);
      const data = Object.fromEntries(formData.entries());

      /* Frontend-only: log and show success. Replace with PHP/API call later. */
      console.log('Appointment request:', data);

      showStatus('Thank you! We\'ll confirm your appointment within one business day.', 'success');
      bookingForm.reset();

      if (dateInput) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        dateInput.min = tomorrow.toISOString().split('T')[0];
      }
    });

    bookingForm.querySelectorAll('.form__input').forEach(function (input) {
      input.addEventListener('blur', function () {
        if (input.hasAttribute('required') || input.value) {
          validateField(input);
        }
      });
    });
  }
})();
