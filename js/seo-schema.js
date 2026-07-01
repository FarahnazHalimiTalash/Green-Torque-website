/**
 * Green Torque — inject JSON-LD structured data for local SEO and reviews
 */
(function () {
  'use strict';

  var seo = window.GREEN_TORQUE_SEO || {};
  var reviewsData = window.GOOGLE_REVIEWS_DATA || {};
  var vehicleMakes = window.VEHICLE_MAKES || [];
  var vehicleData = window.VEHICLE_DATA || {};
  var siteUrl = seo.siteUrl || '';
  var logoUrl = siteUrl + (seo.logoPath || 'images/logo.png');
  var reviews = reviewsData.reviews || [];
  var rating = reviewsData.rating || 5;
  var reviewCount = reviewsData.reviewCount || reviews.length;

  if (!siteUrl) return;

  var businessId = siteUrl + '#business';
  var websiteId = siteUrl + '#website';
  var faqId = siteUrl + '#faq';

  function allVehicleKeywords() {
    var keywords = vehicleMakes.slice();

    Object.keys(vehicleData).forEach(function (make) {
      if (make === 'Other') return;

      (vehicleData[make] || []).forEach(function (model) {
        if (model !== 'Other') {
          keywords.push(make + ' ' + model);
        }
      });
    });

    return keywords;
  }

  function allServices() {
    var main = (seo.services || []).map(function (service) {
      return { name: service.name, description: service.description };
    });
    var hybrid = (seo.hybridServices || []).map(function (service) {
      return { name: service.name, description: service.description };
    });
    var booking = (seo.bookingServices || []).map(function (name) {
      return { name: name, description: name + ' at Green Torque in Sacramento, CA.' };
    });

    return main.concat(hybrid, booking);
  }

  var schemaReviews = reviews.map(function (review) {
    return {
      '@type': 'Review',
      author: {
        '@type': 'Person',
        name: review.author
      },
      reviewRating: {
        '@type': 'Rating',
        ratingValue: String(review.rating || 5),
        bestRating: '5',
        worstRating: '1'
      },
      reviewBody: review.text
    };
  });

  var serviceCatalog = allServices().map(function (service, index) {
    return {
      '@type': 'Offer',
      position: index + 1,
      itemOffered: {
        '@type': 'Service',
        name: service.name,
        description: service.description,
        areaServed: seo.addressLocality,
        provider: { '@id': businessId }
      }
    };
  });

  var graph = [
    {
      '@type': 'WebSite',
      '@id': websiteId,
      url: siteUrl,
      name: seo.shortName || seo.businessName,
      description: seo.description,
      inLanguage: 'en-US',
      publisher: { '@id': businessId }
    },
    {
      '@type': 'AutoRepair',
      '@id': businessId,
      name: seo.businessName,
      alternateName: seo.shortName,
      image: logoUrl,
      url: siteUrl,
      telephone: seo.telephone,
      email: seo.email,
      description: seo.description,
      priceRange: seo.priceRange,
      address: {
        '@type': 'PostalAddress',
        streetAddress: seo.streetAddress,
        addressLocality: seo.addressLocality,
        addressRegion: seo.addressRegion,
        postalCode: seo.postalCode,
        addressCountry: seo.addressCountry
      },
      geo: {
        '@type': 'GeoCoordinates',
        latitude: seo.latitude,
        longitude: seo.longitude
      },
      openingHoursSpecification: [
        {
          '@type': 'OpeningHoursSpecification',
          dayOfWeek: seo.openingHours.days,
          opens: seo.openingHours.opens,
          closes: seo.openingHours.closes
        }
      ],
      areaServed: {
        '@type': 'City',
        name: seo.addressLocality,
        containedInPlace: {
          '@type': 'State',
          name: 'California'
        }
      },
      sameAs: [seo.googleMapsUrl || reviewsData.googleMapsUrl].filter(Boolean),
      knowsAbout: allVehicleKeywords().concat(
        seo.bookingServices || [],
        (seo.services || []).map(function (s) { return s.name; }),
        (seo.hybridServices || []).map(function (s) { return s.name; })
      ),
      hasOfferCatalog: {
        '@type': 'OfferCatalog',
        name: 'Automotive Repair Services in Sacramento',
        itemListElement: serviceCatalog
      }
    }
  ];

  if (reviews.length) {
    graph[1].aggregateRating = {
      '@type': 'AggregateRating',
      ratingValue: String(rating),
      reviewCount: String(reviewCount),
      bestRating: '5',
      worstRating: '1'
    };
    graph[1].review = schemaReviews;
  }

  if (seo.faqs && seo.faqs.length) {
    graph.push({
      '@type': 'FAQPage',
      '@id': faqId,
      mainEntity: seo.faqs.map(function (faq) {
        return {
          '@type': 'Question',
          name: faq.question,
          acceptedAnswer: {
            '@type': 'Answer',
            text: faq.answer
          }
        };
      })
    });
  }

  var script = document.createElement('script');
  script.type = 'application/ld+json';
  script.textContent = JSON.stringify({
    '@context': 'https://schema.org',
    '@graph': graph
  });
  document.head.appendChild(script);
})();
