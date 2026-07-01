/**
 * Green Torque — load live Google reviews via Places API (New)
 */

(function () {
  'use strict';

  var config = window.GREEN_TORQUE_REVIEWS || {};
  var listEl = document.getElementById('reviews-list');
  var summaryEl = document.getElementById('reviews-summary');
  var linkEl = document.getElementById('reviews-google-link');

  if (!listEl) return;

  if (linkEl && config.googleMapsUrl) {
    linkEl.href = config.googleMapsUrl;
    linkEl.target = '_blank';
    linkEl.rel = 'noopener noreferrer';
  }

  function renderStars(rating) {
    var count = Math.round(Number(rating) || 0);
    var stars = '';

    for (var i = 0; i < 5; i += 1) {
      stars += i < count ? '★' : '☆';
    }

    return stars;
  }

  function escapeHtml(text) {
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function showError(message) {
    listEl.innerHTML =
      '<p class="reviews__message reviews__message--error">' + escapeHtml(message) + '</p>';
  }

  function renderFallback() {
    if (summaryEl) {
      summaryEl.hidden = true;
    }

    listEl.innerHTML =
      '<p class="reviews__message">Live Google reviews load automatically once a Google Maps API key is added in <code>js/reviews-config.js</code>. Until then, read what customers are saying on Google.</p>';
  }

  function renderSummary(place) {
    if (!summaryEl) return;

    var rating = place.rating || 0;
    var total = place.userRatingCount || 0;

    summaryEl.innerHTML =
      '<div class="reviews__rating">' +
      '<span class="reviews__score">' + rating.toFixed(1) + '</span>' +
      '<span class="reviews__stars" aria-label="' + rating + ' out of 5 stars">' + renderStars(rating) + '</span>' +
      '<span class="reviews__count">' + total + ' Google review' + (total === 1 ? '' : 's') + '</span>' +
      '</div>';

    summaryEl.hidden = false;

    if (place.googleMapsUri && linkEl) {
      linkEl.href = place.googleMapsUri;
    }
  }

  function renderReviews(reviews) {
    if (!reviews || !reviews.length) {
      listEl.innerHTML =
        '<p class="reviews__message">No Google reviews are available to display yet.</p>';
      return;
    }

    listEl.innerHTML = reviews
      .map(function (review) {
        var author = review.authorAttribution || {};
        var text = review.text && review.text.text ? review.text.text : '';
        var name = author.displayName || 'Google user';
        var time = review.relativePublishTimeDescription || '';
        var photo = author.photoUri
          ? '<img class="review-card__avatar" src="' + escapeHtml(author.photoUri) + '" alt="" width="40" height="40" loading="lazy">'
          : '<div class="review-card__avatar review-card__avatar--placeholder" aria-hidden="true">' + escapeHtml(name.charAt(0)) + '</div>';

        return (
          '<blockquote class="review-card">' +
          '<div class="review-card__header">' +
          photo +
          '<div>' +
          '<div class="review-card__stars" aria-label="' + (review.rating || 0) + ' out of 5 stars">' + renderStars(review.rating) + '</div>' +
          '<footer class="review-card__author">' + escapeHtml(name) + (time ? ' · ' + escapeHtml(time) : '') + '</footer>' +
          '</div>' +
          '</div>' +
          '<p class="review-card__text">"' + escapeHtml(text) + '"</p>' +
          '</blockquote>'
        );
      })
      .join('');
  }

  function fetchReviews() {
    if (!config.apiKey) {
      renderFallback();
      return;
    }

    listEl.innerHTML = '<p class="reviews__message">Loading Google reviews…</p>';

    fetch('https://places.googleapis.com/v1/places:searchText', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Goog-Api-Key': config.apiKey,
        'X-Goog-FieldMask':
          'places.displayName,places.rating,places.userRatingCount,places.googleMapsUri,places.reviews'
      },
      body: JSON.stringify({
        textQuery: config.textQuery,
        maxResultCount: 1
      })
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('Unable to load Google reviews.');
        }
        return response.json();
      })
      .then(function (data) {
        var place = data.places && data.places[0];

        if (!place) {
          throw new Error('Green Torque Google listing was not found.');
        }

        renderSummary(place);
        renderReviews(place.reviews || []);
      })
      .catch(function () {
        showError('Could not load Google reviews. Please try again later or view them directly on Google.');
        renderFallback();
      });
  }

  fetchReviews();
})();
