(function () {
	'use strict';

	function setProductFilter(schedule, filterValue) {
		var filters = Array.prototype.slice.call(schedule.querySelectorAll('[data-home-product-filter]'));
		var cards = Array.prototype.slice.call(schedule.querySelectorAll('[data-home-product-categories]'));

		filters.forEach(function (button) {
			button.classList.toggle('is-active', button.dataset.homeProductFilter === filterValue);
		});

		cards.forEach(function (card) {
			var categories = (card.dataset.homeProductCategories || '').split(',').filter(Boolean);
			var matches = 'all' === filterValue || categories.indexOf(filterValue) !== -1;

			card.hidden = !matches;
		});
	}

	function initProductFilters() {
		document.querySelectorAll('[data-home-products]').forEach(function (schedule) {
			if ('yes' === schedule.dataset.homeProductsReady) {
				return;
			}

			schedule.dataset.homeProductsReady = 'yes';

			schedule.addEventListener('click', function (event) {
				var button = event.target.closest('[data-home-product-filter]');

				if (!button || !schedule.contains(button)) {
					return;
				}

				setProductFilter(schedule, button.dataset.homeProductFilter || 'all');
			});

			setProductFilter(schedule, schedule.dataset.homeDefaultFilter || 'all');
		});
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', initProductFilters);
	} else {
		initProductFilters();
	}
})();
