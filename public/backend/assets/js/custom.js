(function () {
  "use strict";

  /* page loader */
  function hideLoader() {
    const loader = document.getElementById("loader");
    if (loader) {
      loader.classList.add("d-none");
    }
  }

  window.addEventListener("load", hideLoader);
  /* page loader */

  /* tooltip */
  const tooltipTriggerList = document.querySelectorAll(
    '[data-bs-toggle="tooltip"]'
  );
  const tooltipList = [...tooltipTriggerList].map(
    (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
  );

  /* popover  */
  const popoverTriggerList = document.querySelectorAll(
    '[data-bs-toggle="popover"]'
  );
  const popoverList = [...popoverTriggerList].map(
    (popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl)
  );

  /* breadcrumb date range picker */
  if (document.querySelector("#daterange") && typeof flatpickr !== "undefined") {
    flatpickr("#daterange", {
      mode: "range",
      dateFormat: "F, d Y",
      defaultDate: ["May, 01 2024", "May, 30 2024"],
      disableMobile: true
    });
  }
  /* breadcrumb date range picker */

  /* header theme toggle */
  function toggleTheme() {
    let html = document.querySelector("html");
    if (html.getAttribute("data-theme-mode") === "dark") {
      html.setAttribute("data-theme-mode", "light");
      html.setAttribute("data-header-styles", "light");
      html.setAttribute("data-menu-styles", "light");
      if (!localStorage.getItem("primaryRGB")) {
        html.setAttribute("style", "");
      }
      html.removeAttribute("data-bg-theme");
      if (document.querySelector("#switcher-canvas")) {
        document.querySelector("#switcher-light-theme").checked = true;
        document.querySelector("#switcher-menu-light").checked = true;
      }
      document.querySelector("html").style.removeProperty("--body-bg-rgb", localStorage.bodyBgRGB);
      html.style.removeProperty("--body-bg-rgb2");
      html.style.removeProperty("--light-rgb");
      html.style.removeProperty("--form-control-bg");
      html.style.removeProperty("--input-border");
      if (document.querySelector("#switcher-canvas")) {
        document.querySelector("#switcher-header-light").checked = true;
        document.querySelector("#switcher-menu-light").checked = true;
        document.querySelector("#switcher-light-theme").checked = true;
        document.querySelector("#switcher-background4").checked = false;
        document.querySelector("#switcher-background3").checked = false;
        document.querySelector("#switcher-background2").checked = false;
        document.querySelector("#switcher-background1").checked = false;
        document.querySelector("#switcher-background").checked = false;
      }
      localStorage.removeItem("zynixdarktheme");
      localStorage.removeItem("zynixMenu");
      localStorage.removeItem("zynixHeader");
      localStorage.removeItem("bodylightRGB");
      localStorage.removeItem("bodyBgRGB");
      html.setAttribute("data-header-styles", "light");
    } else {
      html.setAttribute("data-theme-mode", "dark");
      html.setAttribute("data-header-styles", "dark");
      if (!localStorage.getItem("primaryRGB")) {
        html.setAttribute("style", "");
      }
      html.setAttribute("data-menu-styles", "dark");
      if (document.querySelector("#switcher-canvas")) {
        document.querySelector("#switcher-dark-theme").checked = true;
        document.querySelector("#switcher-menu-dark").checked = true;
        document.querySelector("#switcher-header-dark").checked = true;
        document.querySelector("#switcher-menu-dark").checked = true;
        document.querySelector("#switcher-header-dark").checked = true;
        document.querySelector("#switcher-dark-theme").checked = true;
        document.querySelector("#switcher-background4").checked = false;
        document.querySelector("#switcher-background3").checked = false;
        document.querySelector("#switcher-background2").checked = false;
        document.querySelector("#switcher-background1").checked = false;
        document.querySelector("#switcher-background").checked = false;
      }
      localStorage.setItem("zynixdarktheme", "true");
      localStorage.setItem("zynixMenu", "dark");
      localStorage.setItem("zynixHeader", "dark");
      localStorage.removeItem("bodylightRGB");
      localStorage.removeItem("bodyBgRGB");
    }
  }
  let layoutSetting = document.querySelector(".layout-setting");
  if (layoutSetting) {
    layoutSetting.addEventListener("click", toggleTheme);
  }
  /* header theme toggle */

  /* Choices JS */
  document.addEventListener("DOMContentLoaded", function () {
    var genericExamples = document.querySelectorAll("[data-trigger]");
    for (let i = 0; i < genericExamples.length; ++i) {
      var element = genericExamples[i];
      if (element.dataset.choicesInit === "1") {
        continue;
      }
      element.dataset.choicesInit = "1";
      var searchDisabled = (element.getAttribute("data-search-disabled") || "").toLowerCase() === "true";
      new Choices(element, {
        allowHTML: false,
        searchEnabled: !searchDisabled,
        searchPlaceholderValue: element.getAttribute("data-search-placeholder") || (window.AppI18n && window.AppI18n.search) || "Cari...",
        itemSelectText: "",
        shouldSort: false,
        placeholder: true,
        placeholderValue: element.getAttribute("data-placeholder") || (window.AppI18n && window.AppI18n.select) || "Pilih...",
        noResultsText: (window.AppI18n && window.AppI18n.noResults) || "Tidak ditemukan",
        noChoicesText: (window.AppI18n && window.AppI18n.noChoices) || "Tidak ada pilihan",
        removeItemButton: element.hasAttribute("multiple"),
      });
    }
  });
  /* Choices JS */

  /* Searchable select (pure JS, reusable) */
  document.addEventListener("DOMContentLoaded", function () {
    var selects = document.querySelectorAll("select[data-searchable-select]");

    function closeAllSearchableSelects(except) {
      document.querySelectorAll(".bpkp-search-select.is-open").forEach(function (wrap) {
        if (except && wrap === except) {
          return;
        }
        wrap.classList.remove("is-open");
      });
    }

    selects.forEach(function (select) {
      if (select.dataset.searchableSelectInit === "1") {
        return;
      }

      select.dataset.searchableSelectInit = "1";
      select.classList.add("d-none");
      var isMultiple = select.multiple;

      var wrapper = document.createElement("div");
      wrapper.className = "bpkp-search-select";

      var input = document.createElement("input");
      input.type = "search";
      input.className = "form-control bpkp-search-select__input";
      input.placeholder = select.getAttribute("data-search-placeholder") || "Cari...";
      input.autocomplete = "off";

      var control = document.createElement("div");
      control.className = "bpkp-search-select__control";

      if (isMultiple) {
        wrapper.classList.add("is-multiple");
      }
      control.appendChild(input);
      
      control.addEventListener("click", function (e) {
        if (!e.target.closest(".chip-remove")) {
          input.focus();
        }
      });

      var dropdown = document.createElement("div");
      dropdown.className = "bpkp-search-select__dropdown";

      select.insertAdjacentElement("afterend", wrapper);
      wrapper.appendChild(control);
      wrapper.appendChild(dropdown);

      function getOptions() {
        return Array.prototype.slice.call(select.options);
      }

      function syncInputFromValue() {
        var selected = select.options[select.selectedIndex];
        if (!isMultiple) {
          input.value = selected ? selected.text : (select.getAttribute("data-placeholder") || "");
        } else {
          input.value = "";
        }
      }

      function renderChips() {
        if (!isMultiple) {
          return;
        }

        Array.prototype.slice.call(control.querySelectorAll('.bpkp-search-select__chip')).forEach(function (oldChip) {
          oldChip.remove();
        });

        Array.prototype.forEach.call(select.selectedOptions, function (option) {
          if (!option.value) {
            return;
          }

          var chip = document.createElement("span");
          chip.className = "bpkp-search-select__chip";
          chip.innerHTML = '<span class="chip-text">' + option.text + '</span><button type="button" class="chip-remove" title="Hapus tag">&times;</button>';
          chip.querySelector(".chip-remove").addEventListener("click", function (e) {
            e.stopPropagation();
            option.selected = false;
            select.dispatchEvent(new Event("change", { bubbles: true }));
            renderOptions(input.value);
            input.focus();
          });
          control.insertBefore(chip, input);
        });
      }

      function renderOptions(query) {
        var q = (query || "").trim().toLowerCase();
        var options = getOptions().filter(function (option) {
          return q === "" || option.text.toLowerCase().includes(q);
        });

        dropdown.innerHTML = "";

        if (!options.length) {
          var empty = document.createElement("button");
          empty.type = "button";
          empty.className = "bpkp-search-select__option is-empty";
          empty.disabled = true;
          empty.textContent = (window.AppI18n && window.AppI18n.noResults) || "Tidak ditemukan";
          dropdown.appendChild(empty);
          return;
        }

        options.forEach(function (option) {
          var item = document.createElement("button");
          item.type = "button";
          item.className = "bpkp-search-select__option d-flex justify-content-between align-items-center";
          item.innerHTML = '<span>' + option.text + '</span>' + (option.selected ? '<span class="text-primary fw-bold">&check;</span>' : '');
          if (option.selected) {
            item.classList.add("is-selected");
          }
          item.addEventListener("click", function () {
            if (isMultiple) {
              option.selected = !option.selected;
            } else {
              select.value = option.value;
            }
            select.dispatchEvent(new Event("change", { bubbles: true }));
            syncInputFromValue();
            renderChips();
            if (!isMultiple) {
              wrapper.classList.remove("is-open");
            } else {
              renderOptions(input.value);
              input.focus();
            }
          });
          dropdown.appendChild(item);
        });
      }

      input.addEventListener("focus", function () {
        closeAllSearchableSelects(wrapper);
        renderOptions("");
        wrapper.classList.add("is-open");
      });

      input.addEventListener("input", function () {
        closeAllSearchableSelects(wrapper);
        renderOptions(input.value);
        wrapper.classList.add("is-open");
      });

      input.addEventListener("blur", function () {
        window.setTimeout(function () {
          syncInputFromValue();
        }, 120);
      });

      select.addEventListener("change", function () {
        syncInputFromValue();
        renderChips();
        renderOptions("");
      });

      syncInputFromValue();
      renderChips();
      renderOptions("");
    });

    document.addEventListener("click", function (event) {
      if (!event.target.closest(".bpkp-search-select")) {
        closeAllSearchableSelects();
      }
    });
  });
  /* Searchable select */

  /* footer year */
  var yearEl = document.getElementById("year");
  if (yearEl) {
    yearEl.innerHTML = new Date().getFullYear();
  }
  /* footer year */

  /* node waves */
  if (typeof Waves !== "undefined") {
    Waves.attach(".btn-wave", ["waves-light"]);
    Waves.init();
  }
  /* node waves */

  /* card with close button */
  let DIV_CARD = ".card";
  let cardRemoveBtn = document.querySelectorAll(
    '[data-bs-toggle="card-remove"]'
  );
  cardRemoveBtn.forEach((ele) => {
    ele.addEventListener("click", function (e) {
      e.preventDefault();
      let $this = this;
      let card = $this.closest(DIV_CARD);
      card.remove();
      return false;
    });
  });
  /* card with close button */

  /* card with fullscreen */
  let cardFullscreenBtn = document.querySelectorAll(
    '[data-bs-toggle="card-fullscreen"]'
  );
  cardFullscreenBtn.forEach((ele) => {
    ele.addEventListener("click", function (e) {
      let $this = this;
      let card = $this.closest(DIV_CARD);
      card.classList.toggle("card-fullscreen");
      card.classList.remove("card-collapsed");
      e.preventDefault();
      return false;
    });
  });
  /* card with fullscreen */

  /* count-up */
  var i = 1;
  setInterval(() => {
    document.querySelectorAll(".count-up").forEach((ele) => {
      if (ele.getAttribute("data-count") >= i) {
        i = i + 1;
        ele.innerText = i;
      }
    });
  }, 10);
  /* count-up */

  /* back to top */
  const scrollToTop = document.querySelector(".scrollToTop");
  const $rootElement = document.documentElement;
  const $body = document.body;
  window.onscroll = () => {
    const scrollTop = window.scrollY || window.pageYOffset;
    const clientHt = $rootElement.scrollHeight - $rootElement.clientHeight;
    if (window.scrollY > 100) {
      scrollToTop.style.display = "flex";
    } else {
      scrollToTop.style.display = "none";
    }
  };
  scrollToTop.onclick = () => {
    window.scrollTo(0, 0);
  };
  /* back to top */

  /* header dropdowns scroll */
  var myHeadernotification = document.getElementById("header-notification-scroll1");
  if (myHeadernotification) {
    new SimpleBar(myHeadernotification, { autoHide: true });
  }

  /* header dropdowns scroll */
  var myHeadernotification2 = document.getElementById("header-notification-scroll2");
  if (myHeadernotification2) {
    new SimpleBar(myHeadernotification2, { autoHide: true });
  }

  /* header dropdowns scroll */
  var myHeadernotification3 = document.getElementById("header-notification-scroll3");
  if (myHeadernotification3) {
    new SimpleBar(myHeadernotification3, { autoHide: true });
  }

  var myHeaderCart = document.getElementById("header-cart-items-scroll");
  if (myHeaderCart) {
    new SimpleBar(myHeaderCart, { autoHide: true });
  }
  /* header dropdowns scroll */

  const headerSearchInput = document.querySelector('#header-search');
  if (headerSearchInput && typeof autoComplete === 'function') {
    const searchUrl = headerSearchInput.getAttribute('data-search-url') || '/course-search';
    const emptyLabel = headerSearchInput.getAttribute('data-empty') || 'Tidak ditemukan';

    function escapeHtml(text) {
      return String(text || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    const headerCourseSearch = new autoComplete({
      selector: '#header-search',
      threshold: 0,
      debounce: 220,
      searchEngine: 'loose',
      data: {
        src: async (query) => {
          try {
            const response = await fetch(searchUrl + '?q=' + encodeURIComponent(query || ''), {
              headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
              },
              credentials: 'same-origin',
            });
            if (!response.ok) {
              return [];
            }
            return await response.json();
          } catch (error) {
            return [];
          }
        },
        keys: ['judul', 'kategori'],
        cache: false,
        filter: (list) => {
          const seen = new Set();
          return list.filter((item) => {
            const id = item?.value?.id;
            if (!id || seen.has(id)) {
              return false;
            }
            seen.add(id);
            return true;
          });
        },
      },
      resultsList: {
        maxResults: 8,
        noResults: true,
        element: (list, data) => {
          if (!data.results.length) {
            const message = document.createElement('div');
            message.setAttribute('class', 'header-course-search__empty');
            message.innerHTML = `<span>${escapeHtml(emptyLabel)}</span>`;
            list.prepend(message);
          }
        },
      },
      resultItem: {
        highlight: true,
        element: (item, data) => {
          const course = data.value || {};
          const titleHtml = data.key === 'judul'
            ? (data.match || escapeHtml(course.judul))
            : escapeHtml(course.judul);
          const metaParts = [course.kategori, course.topics_label].filter(Boolean);
          item.classList.add('header-course-search__item');
          item.innerHTML = ''
            + `<img class="header-course-search__thumb" src="${escapeHtml(course.thumbnail)}" alt="">`
            + `<span class="header-course-search__meta">`
            + `  <span class="header-course-search__title">${titleHtml}</span>`
            + `  <span class="header-course-search__sub">${escapeHtml(metaParts.join(' · '))}</span>`
            + `</span>`;
        },
      },
      events: {
        input: {
          selection: (event) => {
            const selection = event.detail.selection.value;
            if (selection && selection.url) {
              window.location.href = selection.url;
              return;
            }
            headerCourseSearch.input.value = selection?.judul || '';
          },
        },
      },
    });
  }
})();

/* full screen */
var elem = document.documentElement;
window.openFullscreen = function() {
  if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
    requestFullscreen();
  } else {
    exitFullscreen();
  }
}
function requestFullscreen() {
  if (elem.requestFullscreen) {
    elem.requestFullscreen();
  } else if (elem.webkitRequestFullscreen) {
    elem.webkitRequestFullscreen();
  } else if (elem.msRequestFullscreen) {
    elem.msRequestFullscreen();
  }
}
function exitFullscreen() {
  if (document.exitFullscreen) {
    document.exitFullscreen();
  } else if (document.webkitExitFullscreen) {
    document.webkitExitFullscreen();
  } else if (document.msExitFullscreen) {
    document.msExitFullscreen();
  }
}
// Listen for fullscreen change event
document.addEventListener("fullscreenchange", handleFullscreenChange);
function handleFullscreenChange() {
  
  let open = document.querySelector(".full-screen-open");
  let close = document.querySelector(".full-screen-close");

  if (document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
    // Update icon for fullscreen mode
    close.classList.add("d-block");
    close.classList.remove("d-none");
    open.classList.add("d-none");
  } else {
    // Update icon for non-fullscreen mode
    close.classList.remove("d-block");
    open.classList.remove("d-none");
    close.classList.add("d-none");
    open.classList.add("d-block");
  }
}
/* full screen */

/* toggle switches */
let customSwitch = document.querySelectorAll(".toggle");
customSwitch.forEach((e) =>
  e.addEventListener("click", () => {
    e.classList.toggle("on");
  })
);
/* toggle switches */

/* header dropdown close button */

/* for cart dropdown */
const headerbtn = document.querySelectorAll(".dropdown-item-close");

headerbtn.forEach((button) => {
  button.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();

    // Find the closest parent element with class 'dropdown-item'
    const listItem = button.closest('.dropdown-item');
    if (listItem) {
      listItem.remove(); // Remove the list item
    }

    // Update the cart badge and cart data (optional — cart block may be removed)
    const itemCount = document.querySelectorAll(".dropdown-item-close").length;
    const cartData = document.getElementById("cart-data");
    const cartBadge = document.getElementById("cart-icon-badge");
    if (cartData) cartData.innerText = `${itemCount} Items`;
    if (cartBadge) cartBadge.innerText = `${itemCount}`;

    // Check if there are no items left
    if (itemCount === 0) {
      const emptyHeader = document.querySelector(".empty-header-item");
      const emptyItem = document.querySelector(".empty-item");
      if (emptyHeader) emptyHeader.classList.add("d-none");
      if (emptyItem) emptyItem.classList.remove("d-none");
    }
  });
});

/* for cart dropdown */

/* for notifications dropdown */
const headerbtn1 = document.querySelectorAll(".dropdown-item-close1");
headerbtn1.forEach((button) => {
  button.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    button.parentNode.parentNode.parentNode.parentNode.remove();
    document.getElementById("notifiation-data").innerText = `${document.querySelectorAll(".dropdown-item-close1").length
      } Unread`;
    if (document.querySelectorAll(".dropdown-item-close1").length == 0) {
      let elementHide1 = document.querySelector(".empty-header-item1");
      let elementShow1 = document.querySelector(".empty-item1");
      elementHide1.classList.add("d-none");
      elementShow1.classList.remove("d-none");
    }
  });
});

/* for notifications dropdown */


// for nummber of products selected 
var value = 1,
minValue = 0,
maxValue = 30;

let productMinusBtn = document.querySelectorAll(".product-quantity-minus")
let productPlusBtn = document.querySelectorAll(".product-quantity-plus")
productMinusBtn.forEach((element) => {
element.onclick = () => {
    value = Number(element.parentElement.childNodes[3].value)
    if (value > minValue) {
        value = Number(element.parentElement.childNodes[3].value) - 1;
        element.parentElement.childNodes[3].value = value;
    }
}
})
productPlusBtn.forEach((element) => {
element.onclick = () => {
    if (value < maxValue) {
        value = Number(element.parentElement.childNodes[3].value) + 1;
        element.parentElement.childNodes[3].value = value;
    }
}
})