/**
 * Woo Category Read More - Frontend Engine (Vanilla JS)
 * High performance, 0 jQuery dependencies, CWV & INP friendly.
 * Author: Ameeen
 */

(function () {
	'use strict';

	function initWcCatReadMore() {
		var settings = window.wcCatReadMoreSettings || {
			collapsedHeight: 220,
			moreText: 'مشاهده بیشتر',
			lessText: 'نمایش کمتر',
			enableScroll: true,
			scrollOffset: 80,
			targetMode: 'auto',
			customSelectors: ''
		};

		var COLLAPSED_HEIGHT = parseInt(settings.collapsedHeight, 10) || 220;

		// Candidate selectors for Extra Description (Woodmart, Elementor, Rank Math)
		var extraSelectors = [
			'.elementor-widget-wd_shop_archive_extra_description .wd-term-desc',
			'.elementor-widget-wd_shop_archive_extra_description .elementor-widget-container',
			'.elementor-widget-wd_shop_archive_extra_description',
			'.wd-shop-archive-extra-description .wd-term-desc',
			'.wd-shop-archive-extra-description',
			'.wd-term-desc',
			'.term-extra-description',
			'.rank-math-extra-description',
			'.cat-extra-desc',
			'#cat-extra-description'
		];

		// Candidate selectors for Standard Category Description
		var standardSelectors = [
			'.elementor-widget-wd_shop_archive_archive_description .term-description',
			'.elementor-widget-wd_shop_archive_archive_description',
			'.woocommerce-products-header__title + .page-description',
			'.term-description',
			'.taxonomy-description',
			'.woocommerce-category-description'
		];

		// Append custom selectors if provided
		if (settings.customSelectors && settings.customSelectors.trim() !== '') {
			var customList = settings.customSelectors.split(',');
			for (var c = 0; c < customList.length; c++) {
				var trimmed = customList[c].trim();
				if (trimmed) {
					extraSelectors.unshift(trimmed);
				}
			}
		}

		var targetElements = [];

		function findFirstValid(selectors) {
			for (var i = 0; i < selectors.length; i++) {
				var el = document.querySelector(selectors[i]);
				if (el && el.innerText && el.innerText.trim().length > 60) {
					return el;
				}
			}
			return null;
		}

		if (settings.targetMode === 'extra_only') {
			var ex = findFirstValid(extraSelectors);
			if (ex) targetElements.push(ex);
		} else if (settings.targetMode === 'standard_only') {
			var st = findFirstValid(standardSelectors);
			if (st) targetElements.push(st);
		} else if (settings.targetMode === 'both') {
			var exBoth = findFirstValid(extraSelectors);
			if (exBoth) targetElements.push(exBoth);
			var stBoth = findFirstValid(standardSelectors);
			if (stBoth && stBoth !== exBoth) targetElements.push(stBoth);
		} else {
			// 'auto' mode (Default): Prefer Extra Description if exists, otherwise Standard Description
			var foundExtra = findFirstValid(extraSelectors);
			if (foundExtra) {
				targetElements.push(foundExtra);
			} else {
				var foundStd = findFirstValid(standardSelectors);
				if (foundStd) {
					targetElements.push(foundStd);
				}
			}
		}

		if (targetElements.length === 0) {
			return;
		}

		// Apply clamping to each target element
		for (var t = 0; t < targetElements.length; t++) {
			setupClampingOnElement(targetElements[t], settings, COLLAPSED_HEIGHT);
		}
	}

	function setupClampingOnElement(target, settings, collapsedHeight) {
		if (!target || target.getAttribute('data-wccrm-initialized')) {
			return;
		}
		target.setAttribute('data-wccrm-initialized', 'true');

		var fullHeight = target.scrollHeight;

		// If content is short enough, do not clamp or add buttons
		if (fullHeight <= collapsedHeight + 40) {
			return;
		}

		// Add clamped classes & styles
		target.classList.add('wccrm-clamped-box');
		target.style.maxHeight = collapsedHeight + 'px';

		// Create fade mask
		var mask = document.createElement('div');
		mask.className = 'wccrm-fade-mask';
		target.appendChild(mask);

		// Create toggle button container
		var btnWrap = document.createElement('div');
		btnWrap.className = 'wccrm-btn-wrapper';

		var btnId = 'wccrm-btn-' + Math.random().toString(36).substr(2, 9);
		var btn = document.createElement('button');
		btn.type = 'button';
		btn.id = btnId;
		btn.className = 'wccrm-toggle-btn';
		btn.setAttribute('aria-expanded', 'false');

		var targetId = target.id;
		if (!targetId) {
			targetId = 'wccrm-box-' + Math.random().toString(36).substr(2, 9);
			target.id = targetId;
		}
		btn.setAttribute('aria-controls', targetId);

		btn.innerHTML = '<span class="wccrm-btn-text">' + settings.moreText + '</span>' +
			'<svg class="wccrm-arrow-icon" viewBox="0 0 16 16" aria-hidden="true">' +
			'<path d="M8 11L2 5h12z"/>' +
			'</svg>';

		btnWrap.appendChild(btn);
		target.parentNode.insertBefore(btnWrap, target.nextSibling);

		var isExpanded = false;

		btn.addEventListener('click', function () {
			isExpanded = !isExpanded;

			if (isExpanded) {
				// Expand smoothly
				target.style.maxHeight = target.scrollHeight + 'px';
				target.classList.add('wccrm-expanded');
				btn.classList.add('is-active');
				btn.setAttribute('aria-expanded', 'true');
				btn.querySelector('.wccrm-btn-text').textContent = settings.lessText;
			} else {
				// Smooth scroll back up if user scrolled below the top
				if (settings.enableScroll) {
					var rect = target.getBoundingClientRect();
					if (rect.top < 0) {
						var targetTop = window.pageYOffset + rect.top - (settings.scrollOffset || 80);
						window.scrollTo({
							top: Math.max(0, targetTop),
							behavior: 'smooth'
						});
					}
				}

				// Pin to px before collapsing so transition fires
				if (target.style.maxHeight === 'none' || target.style.maxHeight === '') {
					target.style.maxHeight = target.scrollHeight + 'px';
					void target.offsetHeight; // force reflow
				}

				target.style.maxHeight = collapsedHeight + 'px';
				target.classList.remove('wccrm-expanded');
				btn.classList.remove('is-active');
				btn.setAttribute('aria-expanded', 'false');
				btn.querySelector('.wccrm-btn-text').textContent = settings.moreText;
			}
		});

		// Remove max-height restriction after expanding (safe for late images & FAQ accordions)
		target.addEventListener('transitionend', function (e) {
			if (e.propertyName === 'max-height' && isExpanded) {
				target.style.maxHeight = 'none';
			}
		});

		// Listen to FAQ details toggle inside target to avoid clipping
		var detailsList = target.querySelectorAll('details');
		for (var d = 0; d < detailsList.length; d++) {
			detailsList[d].addEventListener('toggle', function () {
				if (isExpanded) {
					target.style.maxHeight = 'none';
				}
			});
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initWcCatReadMore);
	} else {
		initWcCatReadMore();
	}

	// Recheck on window full load (catches late web fonts and external assets)
	window.addEventListener('load', function () {
		initWcCatReadMore();
	});
})();
