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
  const yearEl = document.getElementById('copyright-year');
  const yearSelect = document.getElementById('vehicle-year');
  const makeSelect = document.getElementById('make');
  const modelSelect = document.getElementById('model');
  const serviceSelect = document.getElementById('service');
  const hybridServiceGroup = document.getElementById('hybrid-service-group');
  const hybridServiceSelect = document.getElementById('hybrid-service');
  const submitButton = bookingForm ? bookingForm.querySelector('button[type="submit"]') : null;

  const ADMIN_EMAIL = 'Info@greentorqueev.com';

  const VEHICLE_DATA = {
    Acura: ['ILX', 'Integra', 'MDX', 'RDX', 'TLX', 'Other'],
    Audi: ['A3', 'A4', 'A6', 'Q3', 'Q5', 'Q7', 'e-tron', 'Other'],
    BMW: ['2 Series', '3 Series', '5 Series', 'X1', 'X3', 'X5', 'i4', 'iX', 'Other'],
    Chevrolet: ['Blazer', 'Bolt EV', 'Camaro', 'Equinox', 'Malibu', 'Silverado', 'Suburban', 'Tahoe', 'Traverse', 'Other'],
    Dodge: ['Challenger', 'Charger', 'Durango', 'Other'],
    Ford: ['Bronco', 'Edge', 'Escape', 'Explorer', 'F-150', 'Focus', 'Fusion', 'Mach-E', 'Mustang', 'Ranger', 'Other'],
    GMC: ['Acadia', 'Canyon', 'Sierra', 'Terrain', 'Yukon', 'Other'],
    Honda: ['Accord', 'Civic', 'CR-V', 'Fit', 'HR-V', 'Odyssey', 'Pilot', 'Prologue', 'Ridgeline', 'Other'],
    Hyundai: ['Elantra', 'Ioniq 5', 'Ioniq 6', 'Kona', 'Palisade', 'Santa Fe', 'Sonata', 'Tucson', 'Other'],
    Jeep: ['Cherokee', 'Compass', 'Grand Cherokee', 'Wrangler', 'Other'],
    Kia: ['EV6', 'Forte', 'K5', 'Niro', 'Seltos', 'Sorento', 'Soul', 'Sportage', 'Telluride', 'Other'],
    Lexus: ['ES', 'GX', 'IS', 'NX', 'RX', 'UX', 'Other'],
    Mazda: ['CX-30', 'CX-5', 'CX-50', 'CX-90', 'Mazda3', 'Mazda6', 'Other'],
    'Mercedes-Benz': ['C-Class', 'E-Class', 'EQE', 'EQS', 'GLC', 'GLE', 'Other'],
    Nissan: ['Altima', 'Armada', 'Frontier', 'Leaf', 'Maxima', 'Murano', 'Pathfinder', 'Rogue', 'Sentra', 'Titan', 'Other'],
    Ram: ['1500', '2500', '3500', 'ProMaster', 'Other'],
    Subaru: ['Ascent', 'Crosstrek', 'Forester', 'Impreza', 'Legacy', 'Outback', 'Other'],
    Tesla: ['Model 3', 'Model S', 'Model X', 'Model Y', 'Cybertruck', 'Other'],
    Toyota: ['4Runner', 'bZ4X', 'Camry', 'Corolla', 'Highlander', 'Prius', 'RAV4', 'Sienna', 'Tacoma', 'Tundra', 'Other'],
    Volkswagen: ['Atlas', 'Golf', 'ID.4', 'Jetta', 'Passat', 'Tiguan', 'Other'],
    Volvo: ['EX30', 'S60', 'XC40', 'XC60', 'XC90', 'Other'],
    Other: ['Other']
  };

  function populateSelect(select, options, placeholder) {
    if (!select) return;

    select.innerHTML = '';
    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = placeholder;
    defaultOption.disabled = true;
    defaultOption.selected = true;
    select.appendChild(defaultOption);

    options.forEach(function (option) {
      const el = document.createElement('option');
      el.value = option.value;
      el.textContent = option.label;
      if (option.make) {
        el.dataset.make = option.make;
      }
      select.appendChild(el);
    });
  }

  function populateYearOptions() {
    if (!yearSelect) return;

    const currentYear = new Date().getFullYear();
    const years = [];

    for (let year = currentYear; year >= currentYear - 35; year -= 1) {
      years.push({ value: String(year), label: String(year) });
    }

    populateSelect(yearSelect, years, 'Select year');
  }

  function populateAllModelOptions() {
    if (!modelSelect) return;

    const models = [];

    Object.keys(VEHICLE_DATA).forEach(function (make) {
      VEHICLE_DATA[make].forEach(function (model) {
        models.push({
          value: model,
          label: make === 'Other' ? 'Other' : model + ' (' + make + ')',
          make: make
        });
      });
    });

    models.sort(function (a, b) {
      return a.label.localeCompare(b.label);
    });

    populateSelect(modelSelect, models, 'Select model');
  }

  function filterModelOptions(make) {
    if (!modelSelect) return;

    Array.from(modelSelect.options).forEach(function (option, index) {
      if (index === 0) return;
      option.hidden = Boolean(make) && option.dataset.make !== make;
    });

    modelSelect.value = '';
  }

  populateYearOptions();
  populateAllModelOptions();

  if (makeSelect) {
    makeSelect.addEventListener('change', function () {
      filterModelOptions(makeSelect.value);
    });
  }

  function resetHybridServiceField() {
    if (!hybridServiceGroup || !hybridServiceSelect) return;

    hybridServiceGroup.hidden = true;
    hybridServiceSelect.required = false;
    hybridServiceSelect.selectedIndex = 0;
  }

  function toggleHybridServiceField() {
    if (!serviceSelect || !hybridServiceGroup || !hybridServiceSelect) return;

    if (serviceSelect.value === 'hybrid-ev') {
      hybridServiceGroup.hidden = false;
      hybridServiceSelect.required = true;
      return;
    }

    resetHybridServiceField();
  }

  if (serviceSelect) {
    serviceSelect.addEventListener('change', toggleHybridServiceField);
  }

  resetHybridServiceField();

  function resetVehicleSelects() {
    if (yearSelect) yearSelect.selectedIndex = 0;
    if (makeSelect) makeSelect.selectedIndex = 0;
    if (modelSelect) {
      filterModelOptions('');
      modelSelect.selectedIndex = 0;
    }
    resetHybridServiceField();
  }

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

      toggleHybridServiceField();

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
      const payload = Object.fromEntries(formData.entries());

      payload._subject = 'New Appointment Request - Green Torque';
      payload._template = 'table';
      payload._captcha = 'false';

      if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = 'Sending...';
      }

      fetch('https://formsubmit.co/ajax/' + encodeURIComponent(ADMIN_EMAIL), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json'
        },
        body: JSON.stringify(payload)
      })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('Request failed');
          }
          return response.json();
        })
        .then(function () {
          showStatus('Thank you! Your appointment was confirmed.', 'success');
          bookingForm.reset();
          resetVehicleSelects();

          if (dateInput) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            dateInput.min = tomorrow.toISOString().split('T')[0];
          }
        })
        .catch(function () {
          showStatus('Unable to send your request. Please call (916) 896-9086 for urgent assistance.', 'error');
        })
        .finally(function () {
          if (submitButton) {
            submitButton.disabled = false;
            submitButton.textContent = 'Request Appointment';
          }
        });
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
