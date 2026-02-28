(function () {
  function initHomeFilters() {
    var container = document.getElementById('category-container');
    var firstRow = document.getElementById('category-row-1');
    var blogCards = document.querySelectorAll('.blog-card');
    var resultsStatus = document.getElementById('filter-results-status');

    if (!container || !firstRow || !blogCards.length) {
      return;
    }

    var categoriesData = [];
    try {
      categoriesData = JSON.parse(container.getAttribute('data-categories') || '[]');
    } catch (e) {
      categoriesData = [];
    }

    var currentRow = 1;
    var visibleCategories = 0;
    var maxCategoriesPerRow = 8;
    var maxCategoriesPerRowTablet = 5;
    var maxCategoriesPerRowMobile = 3;

    function getScreenSize() {
      var width = window.innerWidth;
      if (width <= 640) return 'mobile';
      if (width <= 1024) return 'tablet';
      return 'desktop';
    }

    function getMaxCategoriesPerRow() {
      var size = getScreenSize();
      if (size === 'mobile') return maxCategoriesPerRowMobile;
      if (size === 'tablet') return maxCategoriesPerRowTablet;
      return maxCategoriesPerRow;
    }

    function isMobile() {
      return getScreenSize() === 'mobile';
    }

    function getCategoryColor(categoryName) {
      var colorMap = {
        'Uncategorized': { bg: '#f3f4f6', text: '#374151', active: '#6b7280' },
        'Self Improvement': { bg: '#f3f4f6', text: '#374151', active: '#059669' },
        'Signals Of Progress': { bg: '#f3f4f6', text: '#374151', active: '#dc2626' },
        'Awareness': { bg: '#f3f4f6', text: '#374151', active: '#7c3aed' },
        'Psychology': { bg: '#f3f4f6', text: '#374151', active: '#7c3aed' },
        'Mental Health': { bg: '#f3f4f6', text: '#374151', active: '#dc2626' },
        'Health and Fitness': { bg: '#f3f4f6', text: '#374151', active: '#059669' },
        'Discipline & Design': { bg: '#f3f4f6', text: '#374151', active: '#0891b2' },
        'Consciousness': { bg: '#f3f4f6', text: '#374151', active: '#7c3aed' },
        'Inspiring': { bg: '#f3f4f6', text: '#374151', active: '#ca8a04' },
        'The Good Thread': { bg: '#f3f4f6', text: '#374151', active: '#059669' },
        'Wildlife': { bg: '#f3f4f6', text: '#374151', active: '#16a34a' },
        'Art & Creativity': { bg: '#f3f4f6', text: '#374151', active: '#dc2626' },
        'Innovation': { bg: '#f3f4f6', text: '#374151', active: '#0891b2' },
        'Nutrition': { bg: '#f3f4f6', text: '#374151', active: '#16a34a' },
        'Personal Growth Mindset': { bg: '#f3f4f6', text: '#374151', active: '#7c3aed' },
        'Wellness': { bg: '#f3f4f6', text: '#374151', active: '#059669' },
        'Trending': { bg: '#f3f4f6', text: '#374151', active: '#dc2626' },
        'Blogging': { bg: '#f3f4f6', text: '#374151', active: '#0891b2' },
        'Miscellaneous': { bg: '#f3f4f6', text: '#374151', active: '#6b7280' },
        'True Hero Files': { bg: '#f3f4f6', text: '#374151', active: '#ca8a04' },
        'Yoga & Spirituality': { bg: '#f3f4f6', text: '#374151', active: '#7c3aed' },
        'Social Media': { bg: '#f3f4f6', text: '#374151', active: '#0891b2' },
        'Instagram': { bg: '#f3f4f6', text: '#374151', active: '#dc2626' },
        'AI & Tools': { bg: '#f3f4f6', text: '#374151', active: '#7c3aed' }
      };

      return colorMap[categoryName] || { bg: '#f3f4f6', text: '#374151', active: '#6b7280' };
    }

    function setCategoryButtonDefault(button) {
      if (button.textContent === 'See more...') {
        return;
      }
      var colors = getCategoryColor(button.textContent);
      button.classList.remove('active');
      button.setAttribute('aria-pressed', 'false');
      button.style.backgroundColor = colors.bg;
      button.style.color = colors.text;
      button.style.border = '1px solid transparent';
    }

    function setCategoryButtonActive(button) {
      var colors = getCategoryColor(button.textContent);
      button.classList.add('active');
      button.setAttribute('aria-pressed', 'true');
      button.style.backgroundColor = '#f3f4f6';
      button.style.color = '#374151';
      button.style.border = '1px solid ' + colors.active;
    }

    function updateFilterStatus(selectedCategory) {
      if (!resultsStatus) {
        return;
      }

      var visibleCount = 0;
      blogCards.forEach(function (card) {
        if (!card.hidden) {
          visibleCount++;
        }
      });

      var label = selectedCategory === 'all'
        ? 'all categories'
        : '"' + selectedCategory.replace(/-/g, ' ') + '"';
      var noun = visibleCount === 1 ? 'post' : 'posts';
      resultsStatus.textContent = 'Showing ' + visibleCount + ' ' + noun + ' for ' + label + '.';
    }

    function applyFilter(selectedCategory) {
      blogCards.forEach(function (card) {
        if (selectedCategory === 'all') {
          card.hidden = false;
          return;
        }
        var cardCategories = card.getAttribute('data-categories') || '';
        card.hidden = !cardCategories.includes(selectedCategory);
      });
      updateFilterStatus(selectedCategory);
    }

    function createCategoryButton(category, isActive) {
      var button = document.createElement('button');
      var textSize = isMobile() ? 'text-xs' : 'text-sm';
      button.type = 'button';
      button.className = 'category-filter-btn ts-category-filter-btn px-3 py-2 rounded-full ' + textSize + ' font-medium transition-all duration-200 whitespace-nowrap flex-shrink-0';
      button.setAttribute('data-category', category.slug);
      button.setAttribute('aria-controls', 'blog-post-grid');
      button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      button.textContent = category.name;

      setCategoryButtonDefault(button);
      if (isActive) {
        setCategoryButtonActive(button);
      }
      return button;
    }

    function addEventListenersToRow(row) {
      row.querySelectorAll('.category-filter-btn').forEach(function (button) {
        if (button.textContent === 'See more...') {
          return;
        }
        button.addEventListener('click', function () {
          var selectedCategory = this.getAttribute('data-category');
          document.querySelectorAll('.category-filter-btn').forEach(function (btn) {
            setCategoryButtonDefault(btn);
          });
          setCategoryButtonActive(this);
          applyFilter(selectedCategory);
        });
      });
    }

    function createSeeMoreButton() {
      var button = document.createElement('button');
      var textSize = isMobile() ? 'text-xs' : 'text-sm';
      button.type = 'button';
      button.className = 'category-filter-btn ts-see-more-btn px-3 py-2 rounded-full ' + textSize + ' font-medium transition-all duration-200 whitespace-nowrap flex-shrink-0';
      button.textContent = 'See more...';
      button.addEventListener('click', showNextRow);
      return button;
    }

    function showNextRow() {
      currentRow++;
      var newRow = document.createElement('div');
      newRow.id = 'category-row-' + currentRow;
      newRow.className = 'ts-category-row flex justify-start gap-3 px-4 sm:px-0 overflow-x-auto pb-1 mt-4';

      var categoriesInRow = 0;
      while (visibleCategories < categoriesData.length && categoriesInRow < getMaxCategoriesPerRow()) {
        var category = categoriesData[visibleCategories];
        var button = createCategoryButton(category, false);
        newRow.appendChild(button);
        visibleCategories++;
        categoriesInRow++;
      }

      if (visibleCategories < categoriesData.length) {
        newRow.appendChild(createSeeMoreButton());
      }

      container.appendChild(newRow);

      var prevRow = document.getElementById('category-row-' + (currentRow - 1));
      var prevSeeMoreButton = prevRow ? prevRow.querySelector('button:last-child') : null;
      if (prevSeeMoreButton && prevSeeMoreButton.textContent === 'See more...') {
        prevSeeMoreButton.remove();
      }

      addEventListenersToRow(newRow);
    }

    function initializeCategories() {
      firstRow.innerHTML = '';
      currentRow = 1;
      visibleCategories = 0;

      var allButton = createCategoryButton({ slug: 'all', name: 'All' }, true);
      firstRow.appendChild(allButton);

      var categoriesInRow = 0;
      var maxInFirstRow = getMaxCategoriesPerRow() - 1;
      while (visibleCategories < categoriesData.length && categoriesInRow < maxInFirstRow) {
        var category = categoriesData[visibleCategories];
        var button = createCategoryButton(category, false);
        firstRow.appendChild(button);
        visibleCategories++;
        categoriesInRow++;
      }

      if (visibleCategories < categoriesData.length) {
        firstRow.appendChild(createSeeMoreButton());
      }

      if (isMobile() && visibleCategories < categoriesData.length) {
        showNextRow();
      }

      addEventListenersToRow(firstRow);
      applyFilter('all');
    }

    initializeCategories();

    var resizeTimer;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        var extraRows = container.querySelectorAll('[id^="category-row-"]:not(#category-row-1)');
        extraRows.forEach(function (row) {
          row.remove();
        });
        initializeCategories();
      }, 120);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHomeFilters);
  } else {
    initHomeFilters();
  }
})();
