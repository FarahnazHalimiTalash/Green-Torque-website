/**
 * Green Torque — display Google review cards
 */

(function () {
  'use strict';

  var data = window.GOOGLE_REVIEWS_DATA || {};
  var listEl = document.getElementById('reviews-list');
  var summaryEl = document.getElementById('reviews-summary');
  var linkEl = document.getElementById('reviews-google-link');

  if (!listEl) return;

  if (linkEl && data.googleMapsUrl) {
    linkEl.href = data.googleMapsUrl;
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

  var reviews = data.reviews || [];
  var rating = data.rating || 5;
  var total = data.reviewCount || reviews.length;

  if (summaryEl && reviews.length) {
    summaryEl.innerHTML =
      '<div class="reviews__rating">' +
      '<span class="reviews__score">' + Number(rating).toFixed(1) + '</span>' +
      '<span class="reviews__stars" aria-label="' + rating + ' out of 5 stars">' + renderStars(rating) + '</span>' +
      '<span class="reviews__count">' + total + ' Google review' + (total === 1 ? '' : 's') + '</span>' +
      '</div>';
    summaryEl.hidden = false;
  }

  if (!reviews.length) {
    listEl.innerHTML =
      '<p class="reviews__message">Google reviews will appear here once added from your Google Business listing. ' +
      '<a href="' + escapeHtml(data.googleMapsUrl) + '" target="_blank" rel="noopener noreferrer">View reviews on Google Maps</a></p>';
    return;
  }

  listEl.innerHTML = reviews
    .map(function (review, index) {
      var authorId = 'review-author-' + index;

      return (
        '<article class="review-card" aria-labelledby="' + authorId + '">' +
        '<div class="review-card__icon" aria-hidden="true">' +
        '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>' +
        '</div>' +
        '<div class="review-card__stars" aria-label="' + (review.rating || 5) + ' out of 5 stars">' +
        renderStars(review.rating || 5) +
        '</div>' +
        '<h3 class="review-card__author" id="' + authorId + '">' + escapeHtml(review.author) + '</h3>' +
        '<blockquote class="review-card__quote">' +
        '<p class="review-card__text">"' + escapeHtml(review.text) + '"</p>' +
        '</blockquote>' +
        (review.time
          ? '<footer class="review-card__time"><cite class="review-card__source">Google Review</cite> · ' + escapeHtml(review.time) + '</footer>'
          : '<footer class="review-card__time"><cite class="review-card__source">Google Review</cite></footer>') +
        '</article>'
      );
    })
    .join('');
})();
