document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
      const password = toggle.closest('.password-control')?.querySelector('input');
      if (!password) return;
      const isVisible = password.type === 'text';
      password.type = isVisible ? 'password' : 'text';
      toggle.setAttribute('aria-pressed', String(!isVisible));
      toggle.setAttribute('aria-label', isVisible ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });
  });

  const productGallery = document.querySelector('[data-product-gallery]');
  if (productGallery) {
    const slides = Array.from(productGallery.querySelectorAll('[data-product-gallery-slide]'));
    const indicators = Array.from(productGallery.querySelectorAll('[data-gallery-index]'));
    let activeSlide = 0;

    const showProductImage = (index) => {
      activeSlide = (index + slides.length) % slides.length;
      slides.forEach((slide, slideIndex) => {
        slide.hidden = slideIndex !== activeSlide;
      });


      indicators.forEach((indicator, indicatorIndex) => {
        const active = indicatorIndex === activeSlide;
        indicator.classList.toggle('active', active);
        indicator.setAttribute('aria-current', active ? 'true' : 'false');
      });
    };

    productGallery.querySelectorAll('[data-gallery-step]').forEach((button) => {
      button.addEventListener('click', () => showProductImage(activeSlide + Number(button.dataset.galleryStep)));
    });
    indicators.forEach((indicator) => {
      indicator.addEventListener('click', () => showProductImage(Number(indicator.dataset.galleryIndex)));
    });
    if (slides.length > 1) {
      window.setInterval(() => showProductImage(activeSlide + 1), 4000);
    }
  }

  const languageControl = document.querySelector('select[name="language"]');
  languageControl?.addEventListener('change', () => {
    document.body.dataset.lang = languageControl.value;
    document.documentElement.lang = languageControl.value;
    localStorage.setItem('gostore-language', languageControl.value);
    window.applyGoStoreLanguage?.(languageControl.value);
  });

  document.querySelectorAll('input[name="theme"]').forEach((control) => {
    control.addEventListener('change', () => {
      document.body.dataset.theme = control.value;
      localStorage.setItem('gostore-theme', control.value);
      const themeColors = { violeta: '#7657f6', naranja: '#ff8b2c', verde: '#1bc98b', azul: '#2b7cff', oscuro: '#111827' };
      document.querySelector('meta[name="theme-color"]')?.setAttribute('content', themeColors[control.value] || themeColors.violeta);
      document.querySelectorAll('.theme-card').forEach((card) => {
        card.classList.toggle('is-selected', card.contains(control));
      });
      control.form?.requestSubmit();
    });
  });

  window.applyGoStoreLanguage?.(document.body.dataset.lang || 'es');

  const track = document.getElementById('featured-track');
  if (!track) return;
  const cards = Array.from(track.querySelectorAll('.featured-card'));
  const indicators = document.getElementById('carousel-indicators');
  const carouselInterval = 4000;
  const carousel = document.getElementById('featured-carousel');
  const carouselTransition = 650;
  let activeSlide = 0;
  track.style.setProperty('--carousel-transition-duration', `${carouselTransition}ms`);

  const showSlide = (index) => {
    if (cards.length === 0) return;
    activeSlide = (index + cards.length) % cards.length;
    track.style.transform = `translateX(-${activeSlide * 100}%)`;
    indicators?.querySelectorAll('.carousel-indicator').forEach((indicator, indicatorIndex) => {
      indicator.setAttribute('aria-current', indicatorIndex === activeSlide ? 'true' : 'false');
    });
  };

  if (indicators && cards.length > 1) {
    cards.forEach((_, index) => {
      const indicator = document.createElement('button');
      indicator.type = 'button';
      indicator.className = 'carousel-indicator';
      indicator.setAttribute('aria-label', `Mostrar diapositiva ${index + 1}`);
      indicator.setAttribute('aria-current', index === 0 ? 'true' : 'false');
      indicator.addEventListener('click', () => showSlide(index));
      indicators.append(indicator);
    });
  }

  if (cards.length > 1) {
    document.querySelectorAll('[data-scroll]').forEach((button) => {
      button.addEventListener('click', () => showSlide(activeSlide + Number(button.dataset.scroll)));
    });
  }

  if (cards.length > 0) showSlide(0);
  if (cards.length > 1) {
    window.setInterval(() => {
      if (!carousel || !carousel.hidden) showSlide(activeSlide + 1);
    }, carouselInterval);
  }

  const presentationKey = carousel?.dataset.presentationKey;
  const presentationClose = carousel?.querySelector('.presentation-close');
  if (carousel?.dataset.presentationPerLogin === 'true' && !carousel.hidden) {
    document.body.classList.add('presentation-open');
    presentationClose?.focus();
  } else if (carousel?.dataset.presentationPerLogin !== 'true' && presentationKey && !sessionStorage.getItem(presentationKey) && !document.querySelector('.guide-overlay')) {
    sessionStorage.setItem(presentationKey, 'shown');
    carousel.hidden = false;
    carousel.setAttribute('aria-hidden', 'false');
    document.body.classList.add('presentation-open');
    presentationClose?.focus();
  } else if (carousel) {
    carousel.hidden = true;
  }

  const closePresentation = () => {
    if (!carousel) return;
    carousel.hidden = true;
    carousel.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('presentation-open');
  };
  presentationClose?.addEventListener('click', closePresentation);
  carousel?.querySelectorAll('a[href="#productos"]').forEach((link) => {
    link.addEventListener('click', closePresentation);
  });
  carousel?.addEventListener('click', (event) => {
    if (event.target === carousel) closePresentation();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && carousel && !carousel.hidden) closePresentation();
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const productModal = document.getElementById('product-modal');
  const productModalContent = document.getElementById('product-modal-content');
  const productModalClose = productModal?.querySelector('.product-modal-close');
  const productScrollKey = 'gostore-product-background-scroll';
  const savedScroll = sessionStorage.getItem(productScrollKey);

  if (productModal) {
    const backgroundScroll = Number(savedScroll) || 0;
    document.body.style.setProperty('--modal-background-scroll', `-${backgroundScroll}px`);
    document.body.classList.add('product-modal-open');
    productModalContent?.focus({ preventScroll: true });
    if (window.location.hash === '#reviews') {
      const reviews = productModal.querySelector('#reviews');
      if (reviews && productModalContent) {
        window.requestAnimationFrame(() => {
          productModalContent.scrollTop = reviews.offsetTop - productModalContent.offsetTop;
        });
      }
    }
  } else if (savedScroll !== null) {
    sessionStorage.removeItem(productScrollKey);
    window.requestAnimationFrame(() => window.scrollTo(0, Number(savedScroll) || 0));
  }

  document.addEventListener('click', (event) => {
    const target = event.target instanceof Element ? event.target : null;
    const productLink = target?.closest('a[href*="product="]');
    if (!productLink || productModal) return;
    const destination = new URL(productLink.href, window.location.href);
    if (destination.origin === window.location.origin && destination.searchParams.has('product')) {
      sessionStorage.setItem(productScrollKey, String(window.scrollY));
    }
  });

  productModal?.addEventListener('click', (event) => {
    if (event.target === productModal && productModalClose) {
      window.location.href = productModalClose.href;
    }
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && productModal && productModalClose) {
      window.location.href = productModalClose.href;
    }
  });

  const loader = document.getElementById('system-loader');
  const loaderMessage = document.getElementById('loader-message');
  const confirmDialog = document.getElementById('confirm-dialog');
  const confirmMessage = document.getElementById('confirm-message');
  const confirmTitle = document.getElementById('confirm-title');
  const confirmCancel = document.querySelector('[data-confirm-cancel]');
  const confirmAccept = document.querySelector('[data-confirm-accept]');
  let pendingConfirmationForm = null;
  let confirmationReturnFocus = null;

  const showLoader = (message) => {
    if (!loader) return;
    if (loaderMessage) loaderMessage.textContent = message;
    loader.hidden = false;
    loader.setAttribute('aria-hidden', 'false');
  };

  const closeConfirmation = () => {
    if (!confirmDialog) return;
    const returnFocus = confirmationReturnFocus;
    confirmDialog.hidden = true;
    document.body.classList.remove('dialog-open');
    pendingConfirmationForm = null;
    confirmationReturnFocus = null;
    if (returnFocus instanceof HTMLElement && returnFocus.isConnected) returnFocus.focus();
  };

  confirmCancel?.addEventListener('click', () => closeConfirmation());
  confirmAccept?.addEventListener('click', () => {
    const form = pendingConfirmationForm;
    closeConfirmation();
    if (!form) return;
    form.dataset.confirmed = 'true';
    form.requestSubmit();
  });
  confirmDialog?.addEventListener('click', (event) => {
    if (event.target === confirmDialog) closeConfirmation();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && confirmDialog && !confirmDialog.hidden) closeConfirmation();
  });

  const actionMessages = {
    login: 'Iniciando sesión...',
    register: 'Creando tu cuenta...',
    logout: 'Cerrando sesión...',
    profile: 'Actualizando tu perfil...',
    settings: 'Guardando tus ajustes...',
    guide_show: 'Abriendo la guía...',
    notification_read: 'Abriendo notificación...',
    cart_add: 'Actualizando tu carrito...',
    checkout: 'Registrando tu pedido...',
    product_save: 'Guardando producto...',
    product_delete: 'Eliminando producto...',
    carousel_save: 'Publicando imagen...',
    carousel_delete: 'Retirando imagen...',
    product_inquiry: 'Enviando tu consulta al administrador...',
    review: 'Publicando tu reseña...',
    vote: 'Guardando tu opinión...',
    order_status: 'Actualizando el pedido...'
  };

  const productImagesInput = document.querySelector('[data-product-images]');
  productImagesInput?.addEventListener('change', () => {
    const selectedCount = productImagesInput.files?.length || 0;
    const isEditing = Number(productImagesInput.form?.querySelector('input[name="product_id"]')?.value) > 0;
    productImagesInput.setCustomValidity(
      selectedCount === 0 && isEditing
        ? ''
        : (selectedCount < 2 || selectedCount > 4 ? 'Selecciona entre 2 y 4 imágenes.' : '')
    );
  });

  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.matches('[data-chat-form]')) return;
    if (form.dataset.confirm && form.dataset.confirmed !== 'true') {
      event.preventDefault();
      pendingConfirmationForm = form;
      confirmationReturnFocus = document.activeElement;
      if (confirmTitle) confirmTitle.textContent = 'Confirmar acción';
      if (confirmAccept) confirmAccept.textContent = 'Confirmar';
      if (confirmMessage) confirmMessage.textContent = form.dataset.confirm;
      if (confirmDialog) {
        confirmDialog.hidden = false;
        document.body.classList.add('dialog-open');
        confirmCancel?.focus();
      }
      return;
    }
    delete form.dataset.confirmed;
    const action = form.querySelector('input[name="action"]')?.value;
    showLoader(actionMessages[action] || (form.classList.contains('search-form') ? 'Buscando productos...' : 'Procesando tu solicitud...'));
  });

  document.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
    if (!link || link.target === '_blank' || link.hasAttribute('download')) return;
    const destination = new URL(link.href, window.location.href);
    if (destination.origin !== window.location.origin || (destination.pathname === window.location.pathname && destination.search === window.location.search && destination.hash)) return;
    showLoader('Cargando página...');
  });

  document.querySelectorAll('.alert').forEach((alert) => {
    const dismiss = alert.querySelector('.alert-dismiss');
    dismiss?.addEventListener('click', () => alert.remove());
    if (alert.classList.contains('alert-success')) {
      window.setTimeout(() => alert.remove(), 7000);
    }
  });

  window.addEventListener('pageshow', () => {
    if (!loader) return;
    loader.hidden = true;
    loader.setAttribute('aria-hidden', 'true');
  });

  const searchInput = document.querySelector('.search-form input[name="q"]');
  const productCards = Array.from(document.querySelectorAll('#product-grid .product-card'));
  const hasCatalog = Boolean(document.getElementById('product-grid'));
  const resultCount = document.getElementById('catalog-result-count');
  const emptyState = document.getElementById('catalog-empty');
  const catalogTitle = document.getElementById('catalog-title');
  const clearFilters = document.getElementById('clear-filters');
  const categoryLinks = Array.from(document.querySelectorAll('[data-category-filter]'));
  const catalogPromos = Array.from(document.querySelectorAll('[data-catalog-promo]'));
  const menu = document.getElementById('store-menu');
  const menuToggle = document.querySelector('.menu-toggle');
  const menuBackdrop = document.querySelector('.menu-backdrop');
  const menuClosers = document.querySelectorAll('[data-close-menu]');
  let activeCategory = document.body.dataset.category || '';

  const normalize = (value) => value
    .toLocaleLowerCase('es')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '');

  const updateCatalogUrl = (replace = false) => {
    if (!hasCatalog) return;
    const url = new URL(window.location.href);
    url.search = '';
    if (searchInput && searchInput.value.trim()) {
      url.searchParams.set('q', searchInput.value.trim());
    }
    if (activeCategory) url.searchParams.set('category', activeCategory);
    window.history[replace ? 'replaceState' : 'pushState']({}, '', url);
  };

  const applyCatalogFilters = () => {
    const rawQuery = searchInput?.value.trim() || '';
    const query = normalize(rawQuery);
    const hasActiveFilter = Boolean(activeCategory || rawQuery);
    let visibleCount = 0;

    catalogPromos.forEach((section) => {
      section.hidden = hasActiveFilter;
    });

    productCards.forEach((card) => {
      const matchesCategory = !activeCategory || card.dataset.category === activeCategory;
      const matchesSearch = normalize(card.dataset.search || '').includes(query);
      card.hidden = !matchesCategory || !matchesSearch;
      if (!card.hidden) visibleCount += 1;
    });

    if (resultCount) {
      resultCount.textContent = `${visibleCount} producto${visibleCount === 1 ? '' : 's'} para descubrir.`;
    }
    if (emptyState) emptyState.hidden = visibleCount > 0;
    if (catalogTitle) {
      catalogTitle.textContent = searchInput?.value.trim()
        ? 'Resultados de búsqueda'
        : activeCategory || 'Explora todos los productos';
    }
    if (clearFilters) clearFilters.hidden = !activeCategory && !query;
    categoryLinks.forEach((link) => {
      link.classList.toggle('active', link.dataset.categoryFilter === activeCategory);
      if (link.dataset.categoryFilter === activeCategory) {
        link.setAttribute('aria-current', 'page');
      } else {
        link.removeAttribute('aria-current');
      }
    });
  };

  const closeMenu = () => {
    menu?.classList.remove('is-open');
    menuBackdrop?.classList.remove('is-open');
    if (menuBackdrop) menuBackdrop.hidden = true;
    menuToggle?.setAttribute('aria-expanded', 'false');
    menu?.setAttribute('aria-hidden', 'true');
    if (menu) menu.inert = true;
    document.body.classList.remove('menu-visible');
  };

  menuToggle?.addEventListener('click', () => {
    const isOpen = menu?.classList.toggle('is-open') || false;
    if (menuBackdrop) {
      menuBackdrop.hidden = !isOpen;
      menuBackdrop.classList.toggle('is-open', isOpen);
    }
    menuToggle.setAttribute('aria-expanded', String(isOpen));
    menu?.setAttribute('aria-hidden', String(!isOpen));
    if (menu) menu.inert = !isOpen;
    document.body.classList.toggle('menu-visible', isOpen);
  });
  menuClosers.forEach((button) => button.addEventListener('click', closeMenu));
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeMenu();
      closeConfirmation();
    }
  });

  categoryLinks.forEach((link) => {
    link.addEventListener('click', (event) => {
      if (!document.getElementById('product-grid')) return;
      event.preventDefault();
      activeCategory = link.dataset.categoryFilter || '';
      applyCatalogFilters();
      updateCatalogUrl();
      closeMenu();
    });
  });

  searchInput?.addEventListener('input', () => {
    if (!hasCatalog) return;
    applyCatalogFilters();
    updateCatalogUrl(true);
  });
  clearFilters?.addEventListener('click', (event) => {
    event.preventDefault();
    activeCategory = '';
    if (searchInput) searchInput.value = '';
    applyCatalogFilters();
    updateCatalogUrl();
  });
  window.addEventListener('popstate', () => {
    if (!hasCatalog) return;
    const url = new URL(window.location.href);
    activeCategory = url.searchParams.get('category') || '';
    if (searchInput) searchInput.value = url.searchParams.get('q') || '';
    applyCatalogFilters();
  });

  applyCatalogFilters();

  const paymentInstructions = document.getElementById('payment-instructions');
  const paymentCopy = {
    yape: '<b>Yape:</b> paga al <b>925 869 388</b> (Administrador GoStore). El pedido quedará pendiente hasta que se confirme el pago.',
    plin: '<b>Plin:</b> envía el pago al <b>925 869 388</b>. El pedido quedará pendiente hasta que se confirme el pago.',
    bbva: '<b>BBVA:</b> coordina los datos de transferencia con el administrador al <b>925 869 388</b>.',
    contra_entrega: '<b>Boleta de compra:</b> revisa tu resumen y confirma el pedido. Pagarás al recibirlo; después podrás avisar al administrador por WhatsApp.'
  };
  document.querySelectorAll('input[name="payment_method"]').forEach((radio) => {
    radio.addEventListener('change', () => {
      if (!paymentInstructions) return;
      const receipt = document.getElementById('receipt-template');
      paymentInstructions.innerHTML = paymentCopy[radio.value] || '';
      if (receipt) {
        paymentInstructions.innerHTML += receipt.innerHTML;
      }
      paymentInstructions.hidden = false;
      const submit = document.querySelector('.checkout-form button[type="submit"]');
      if (submit) {
        submit.innerHTML = radio.value === 'contra_entrega'
          ? 'Aceptar boleta y continuar · ' + submit.dataset.formattedTotal + ' <span>→</span>'
          : 'Confirmar pedido · ' + submit.dataset.formattedTotal + ' <span>→</span>';
      }
    });
  });

  const modal = document.getElementById('whatsapp-modal');
  const orderReceiptModal = document.getElementById('order-receipt-modal');
  document.querySelectorAll('[data-dismiss-modal]').forEach((button) => {
    button.addEventListener('click', () => {
      modal?.remove();
      orderReceiptModal?.remove();
    });
  });
  modal?.addEventListener('click', (event) => {
    if (event.target === modal) modal.remove();
  });
  orderReceiptModal?.addEventListener('click', (event) => {
    if (event.target === orderReceiptModal) orderReceiptModal.remove();
  });

  const notificationMenu = document.querySelector('.notification-menu');
  const refreshNotifications = async () => {
    if (!notificationMenu) return;
    const response = await fetch('index.php?notifications=1', {
      credentials: 'same-origin',
      cache: 'no-store',
      headers: { Accept: 'application/json' }
    });
    if (!response.ok) throw new Error(`No se pudieron actualizar las notificaciones (${response.status}).`);
    const data = await response.json();
    const trigger = notificationMenu.querySelector('.notification-trigger');
    const panel = notificationMenu.querySelector('.notification-panel');
    const csrfToken = document.querySelector('input[name="csrf"]')?.value;
    if (!trigger || !panel || !csrfToken) return;

    let indicator = trigger.querySelector('.notification-indicator');
    if (data.unreadCount > 0 && !indicator) {
      indicator = document.createElement('span');
      indicator.className = 'notification-indicator';
      indicator.setAttribute('aria-hidden', 'true');
      trigger.append(indicator);
    } else if (data.unreadCount === 0) {
      indicator?.remove();
    }

    const countLabel = panel.querySelector('.notification-panel-heading small');
    if (countLabel) countLabel.textContent = data.unreadCount > 0 ? 'Tienes novedades' : 'Actividad reciente';
    trigger.setAttribute('aria-label', data.unreadCount > 0
      ? 'Notificaciones, hay notificaciones nuevas'
      : 'Notificaciones');
    let list = panel.querySelector('.notification-list');
    let emptyMessage = panel.querySelector('.notification-empty');
    if (data.notifications.length === 0) {
      list?.remove();
      if (!emptyMessage) {
        emptyMessage = document.createElement('p');
        emptyMessage.className = 'notification-empty';
        emptyMessage.textContent = 'Todavía no tienes notificaciones.';
        panel.append(emptyMessage);
      }
      return;
    }
    emptyMessage?.remove();
    if (!list) {
      list = document.createElement('div');
      list.className = 'notification-list';
      panel.append(list);
    }
    list.replaceChildren();
    data.notifications.forEach((notification) => {
      const form = document.createElement('form');
      form.method = 'post';
      const csrf = document.createElement('input');
      csrf.type = 'hidden';
      csrf.name = 'csrf';
      csrf.value = csrfToken;
      const action = document.createElement('input');
      action.type = 'hidden';
      action.name = 'action';
      action.value = 'notification_read';
      const id = document.createElement('input');
      id.type = 'hidden';
      id.name = 'notification_id';
      id.value = String(notification.id);
      const item = document.createElement('div');
      item.className = `notification-item${notification.read_at === null ? ' unread' : ''}`;
      const icon = document.createElement('span');
      icon.className = `notification-icon ${notification.type === 'order' ? 'order' : 'product'}`;
      icon.setAttribute('aria-hidden', 'true');
      icon.textContent = notification.type === 'order' ? '↗' : '+';
      const copy = document.createElement('span');
      copy.className = 'notification-copy';
      const title = document.createElement('b');
      title.textContent = notification.type === 'order' ? 'Nuevo pedido' : 'Nuevo producto';
      const message = document.createElement('span');
      message.textContent = notification.message;
      const date = document.createElement('small');
      date.textContent = new Date(notification.created_at.replace(' ', 'T')).toLocaleString('es-PE');
      copy.append(title, message, date);
      const goButton = document.createElement('button');
      goButton.type = 'submit';
      goButton.className = 'notification-go';
      goButton.append(document.createTextNode('Ir '));
      const arrow = document.createElement('span');
      arrow.setAttribute('aria-hidden', 'true');
      arrow.textContent = '→';
      goButton.append(arrow);
      item.append(icon, copy, goButton);
      form.append(csrf, action, id, item);
      list.append(form);
    });
  };
  if (notificationMenu) {
    const positionNotificationPanel = () => {
      if (!notificationMenu.open || window.innerWidth > 760) return;
      const trigger = notificationMenu.querySelector('.notification-trigger');
      if (trigger) {
        notificationMenu.style.setProperty(
          '--notification-panel-top',
          `${trigger.getBoundingClientRect().bottom + 8}px`
        );
      }
    };
    notificationMenu.addEventListener('toggle', positionNotificationPanel);
    window.addEventListener('resize', positionNotificationPanel);
    window.setInterval(() => {
      refreshNotifications().catch((error) => console.error(error));
    }, 30000);
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const widget = document.querySelector('[data-chat-widget]');
  const choice = document.querySelector('[data-chat-choice]');
  let sendConsultation = null;

  if (widget) {
    const role = widget.dataset.role;
    const endpoint = widget.dataset.endpoint;
    const token = widget.querySelector('[data-chat-csrf] input[name="csrf"]')?.value || '';
    const windowPanel = widget.querySelector('[data-chat-window]');
    const launcher = widget.querySelector('[data-chat-toggle]');
    const messages = widget.querySelector('[data-chat-messages]');
    const composer = widget.querySelector('[data-chat-composer]');
    const orderTemplateButton = widget.querySelector('[data-chat-order-template]');
    const form = widget.querySelector('[data-chat-form]');
    const attachment = widget.querySelector('[data-chat-attachment]');
    const usersPanel = widget.querySelector('[data-chat-users]');
    const notice = widget.querySelector('[data-chat-notice]');
    const deleteConfirm = widget.querySelector('[data-chat-delete-confirm]');
    const deleteConfirmCancel = widget.querySelector('[data-chat-delete-cancel]');
    const deleteConfirmAccept = widget.querySelector('[data-chat-delete-accept]');
    const editDialog = widget.querySelector('[data-chat-message-edit]');
    const editTextarea = widget.querySelector('[data-chat-message-edit-text]');
    const editError = widget.querySelector('[data-chat-message-edit-error]');
    const editCancel = widget.querySelector('[data-chat-message-edit-cancel]');
    const editSave = widget.querySelector('[data-chat-message-edit-save]');
    const messageMenu = widget.querySelector('[data-chat-message-menu]');
    const messageEditAction = widget.querySelector('[data-chat-message-edit-action]');
    const messageDeleteAction = widget.querySelector('[data-chat-message-delete-action]');
    const adminMode = role === 'admin' || role === 'superadmin';
    const supervisorMode = role === 'superadmin';
    let activePeer = null;
    let lastMessageId = 0;
    let lastPollAt = 0;
    let roster = [];
    let pendingFile = null;
    let pollInProgress = false;
    let typingTimer = 0;
    let deleteConfirmResolve = null;
    let deleteConfirmReturnFocus = null;
    let editDialogResolve = null;
    let editDialogReturnFocus = null;
    let contextMessage = null;
    let longPressTimer = 0;
    let longPressStart = null;
    let suppressContextUntil = 0;

    const showNotice = (message) => {
      if (!notice) return;
      notice.textContent = message;
      notice.hidden = message === '';
    };

    const setOpen = (open) => {
      if (!windowPanel || !launcher) return;
      windowPanel.hidden = !open;
      launcher.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open && adminMode) widget.querySelector('.chat-user-list')?.classList.add('is-visible');
      if (open && !adminMode) composer?.focus();
    };

    const scrollToLatest = () => {
      if (messages) messages.scrollTop = messages.scrollHeight;
    };

    const closeMessageMenu = () => {
      if (!messageMenu) return;
      messageMenu.hidden = true;
      contextMessage = null;
    };

    const openMessageMenu = (message, clientX, clientY) => {
      if (!messageMenu || !windowPanel) return;
      contextMessage = message;
      const bounds = messageMenu.parentElement.getBoundingClientRect();
      messageMenu.hidden = false;
      messageEditAction.hidden = message.message_type !== 'text';
      const menuBounds = messageMenu.getBoundingClientRect();
      const x = clientX - bounds.left;
      const y = clientY - bounds.top;
      messageMenu.style.left = `${Math.max(8, Math.min(x, bounds.width - menuBounds.width - 8))}px`;
      messageMenu.style.top = `${Math.max(8, Math.min(y, bounds.height - menuBounds.height - 8))}px`;
      messageEditAction.focus();
    };

    const ownMessageFromTarget = (target) => {
      const item = target instanceof Element ? target.closest('.chat-message.mine') : null;
      if (!item || !messages?.contains(item)) return null;
      const message = (item.dataset.messageText ?? '').trim();
      return {
        id: Number(item.dataset.messageId),
        message_text: message,
        message_type: item.dataset.messageType || 'text',
      };
    };

    messageEditAction?.addEventListener('click', () => {
      const message = contextMessage;
      closeMessageMenu();
      if (message) editMessage(message.id, message.message_text);
    });
    messageDeleteAction?.addEventListener('click', () => {
      const message = contextMessage;
      closeMessageMenu();
      if (message) deleteMessage(message.id);
    });
    document.addEventListener('click', (event) => {
      if (messageMenu && !messageMenu.hidden && !messageMenu.contains(event.target)) closeMessageMenu();
    });
    messages?.addEventListener('contextmenu', (event) => {
      const message = ownMessageFromTarget(event.target);
      if (!message) return;
      event.preventDefault();
      if (Date.now() < suppressContextUntil) return;
      openMessageMenu(message, event.clientX, event.clientY);
    });
    messages?.addEventListener('pointerdown', (event) => {
      if (event.pointerType !== 'touch' || event.button !== 0) return;
      const message = ownMessageFromTarget(event.target);
      if (!message) return;
      clearTimeout(longPressTimer);
      longPressStart = { x: event.clientX, y: event.clientY };
      longPressTimer = window.setTimeout(() => {
        suppressContextUntil = Date.now() + 1000;
        openMessageMenu(message, event.clientX, event.clientY);
        longPressTimer = 0;
      }, 550);
    });
    messages?.addEventListener('pointermove', (event) => {
      if (!longPressStart) return;
      if (Math.hypot(event.clientX - longPressStart.x, event.clientY - longPressStart.y) > 10) {
        clearTimeout(longPressTimer);
        longPressTimer = 0;
        longPressStart = null;
      }
    });
    ['pointerup', 'pointercancel', 'pointerleave'].forEach((eventName) => {
      messages?.addEventListener(eventName, () => {
        clearTimeout(longPressTimer);
        longPressTimer = 0;
        longPressStart = null;
      });
    });
    messages?.addEventListener('scroll', closeMessageMenu);
    window.addEventListener('resize', closeMessageMenu);
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && messageMenu && !messageMenu.hidden) closeMessageMenu();
    });

    const closeDeleteConfirm = (confirmed) => {
      if (!deleteConfirm) return;
      const resolve = deleteConfirmResolve;
      const returnFocus = deleteConfirmReturnFocus;
      deleteConfirm.hidden = true;
      deleteConfirmResolve = null;
      deleteConfirmReturnFocus = null;
      if (returnFocus instanceof HTMLElement && returnFocus.isConnected) returnFocus.focus();
      resolve?.(confirmed);
    };

    const askToDeleteMessage = () => new Promise((resolve) => {
      if (!deleteConfirm) {
        resolve(false);
        return;
      }
      deleteConfirmResolve = resolve;
      deleteConfirmReturnFocus = document.activeElement;
      deleteConfirm.hidden = false;
      deleteConfirmCancel?.focus();
    });

    deleteConfirmCancel?.addEventListener('click', () => closeDeleteConfirm(false));
    deleteConfirmAccept?.addEventListener('click', () => closeDeleteConfirm(true));
    deleteConfirm?.addEventListener('click', (event) => {
      if (event.target === deleteConfirm) closeDeleteConfirm(false);
    });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && deleteConfirm && !deleteConfirm.hidden) closeDeleteConfirm(false);
    });

    const closeEditDialog = (text = null) => {
      if (!editDialog) return;
      const resolve = editDialogResolve;
      const returnFocus = editDialogReturnFocus;
      editDialog.hidden = true;
      editDialogResolve = null;
      editDialogReturnFocus = null;
      if (returnFocus instanceof HTMLElement && returnFocus.isConnected) returnFocus.focus();
      resolve?.(text);
    };

    const openEditDialog = (currentText) => new Promise((resolve) => {
      if (!editDialog || !editTextarea) {
        resolve(null);
        return;
      }
      editDialogResolve = resolve;
      editDialogReturnFocus = document.activeElement;
      editTextarea.value = currentText;
      if (editError) {
        editError.textContent = '';
        editError.hidden = true;
      }
      editDialog.hidden = false;
      editTextarea.focus();
      editTextarea.setSelectionRange(editTextarea.value.length, editTextarea.value.length);
    });

    editCancel?.addEventListener('click', () => closeEditDialog());
    editDialog?.addEventListener('click', (event) => {
      if (event.target === editDialog) closeEditDialog();
    });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && editDialog && !editDialog.hidden) closeEditDialog();
    });
    editSave?.addEventListener('click', () => {
      const text = editTextarea?.value.trim() || '';
      const error = text === ''
        ? 'El mensaje no puede quedar vacío.'
        : (text.length > 3000 ? 'El mensaje no puede superar los 3000 caracteres.' : '');
      if (error) {
        if (editError) {
          editError.textContent = error;
          editError.hidden = false;
        }
        editTextarea?.focus();
        return;
      }
      closeEditDialog(text);
    });

    const editMessage = async (messageId, currentText) => {
      const nextText = await openEditDialog(currentText ?? '');
      if (nextText === null) return;
      const trimmed = nextText.trim();
      try {
        await postChatAction('edit_message', { message_id: messageId, message_text: trimmed });
        lastMessageId = 0;
        if (messages) messages.replaceChildren();
        await pollState();
      } catch (error) {
        showNotice(error.message || 'No se pudo editar el mensaje.');
      }
    };

    const deleteMessage = async (messageId) => {
      const confirmed = await askToDeleteMessage();
      if (!confirmed) return;
      try {
        await postChatAction('delete_message', { message_id: messageId });
        lastMessageId = 0;
        if (messages) messages.replaceChildren();
        await pollState();
      } catch (error) {
        showNotice(error.message || 'No se pudo eliminar el mensaje.');
      }
    };

    const appendMessage = (message) => {
      if (!messages || Number(message.id) <= lastMessageId) return;
      const item = document.createElement('article');
      const mine = Number(message.sender_user_id) === Number(widget.dataset.userId);
      item.className = `chat-message${mine ? ' mine' : ''}`;
      item.dataset.messageId = String(message.id);
      item.dataset.messageText = message.message_text || '';
      item.dataset.messageType = message.message_type;
      if (adminMode) {
        const author = document.createElement('b');
        author.className = 'chat-message-author';
        author.textContent = mine ? 'Tú' : message.sender_name;
        item.append(author);
      }
      if (message.message_text) {
        const text = document.createElement('p');
        text.textContent = message.message_text;
        item.append(text);
      }
      if (message.media_url) {
        if (message.message_type === 'image') {
          const image = document.createElement('img');
          image.src = message.media_url;
          image.alt = 'Imagen adjunta';
          image.loading = 'lazy';
          item.append(image);
        } else {
          const video = document.createElement('video');
          video.controls = true;
          video.preload = 'metadata';
          video.playsInline = true;
          const source = document.createElement('source');
          source.src = message.media_url;
          video.append(source);
          item.append(video);
        }
      }
      const meta = document.createElement('small');
      const sentAt = new Date(String(message.created_at).replace(' ', 'T'));
      meta.textContent = `${sentAt.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}${mine && message.read_at ? ' · ✓✓' : ''}`;
      item.append(meta);
      messages.append(item);
      lastMessageId = Number(message.id);
      scrollToLatest();
    };

    const renderUsers = (users) => {
      if (!usersPanel || !adminMode) return;
      roster = users;
      usersPanel.replaceChildren();
      if (!users.length) {
        const empty = document.createElement('p');
        empty.className = 'chat-empty-users';
        empty.textContent = 'No hay usuarios en línea ni conversaciones.';
        usersPanel.append(empty);
        return;
      }
      users.forEach((user) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `chat-user-option${Number(activePeer?.id) === Number(user.id) ? ' active' : ''}`;
        button.dataset.peerId = String(user.id);
        const dot = document.createElement('i');
        dot.className = `chat-user-dot${user.online ? ' online' : ''}`;
        const details = document.createElement('span');
        details.className = 'chat-user-details';
        const name = document.createElement('b');
        name.textContent = user.full_name || user.username;
        const status = document.createElement('small');
        status.textContent = user.online ? 'En línea' : (user.last_message_at ? 'Conversación reciente' : 'Desconectado');
        details.append(name, status);
        button.append(dot, details);
        if (user.unread_count > 0) {
          const unread = document.createElement('span');
          unread.className = 'chat-user-unread';
          unread.textContent = String(user.unread_count);
          button.append(unread);
        }
        button.addEventListener('click', () => {
          if (Number(activePeer?.id) === Number(user.id)) return;
          activePeer = user;
          lastMessageId = 0;
          if (messages) messages.replaceChildren();
          widget.querySelector('[data-chat-title]').textContent = user.full_name || user.username;
          widget.querySelector('[data-chat-peer-title]').textContent = user.username;
          widget.querySelector('[data-chat-peer-status]').textContent = supervisorMode ? 'Solo lectura · conversación supervisada' : (user.online ? 'En línea' : 'Desconectado');
          if (!supervisorMode) {
            composer.disabled = false;
            widget.querySelector('.chat-send-button').disabled = false;
          }
          showNotice('');
          renderUsers(roster);
          pollState().catch((error) => showNotice(error.message));
          if (!supervisorMode) composer?.focus();
        });
        usersPanel.append(button);
      });
    };

    const pollState = async () => {
      if (pollInProgress || document.visibilityState === 'hidden') return;
      pollInProgress = true;
      lastPollAt = Date.now();
      try {
        const url = new URL(endpoint, window.location.href);
        if (adminMode && activePeer) url.searchParams.set('peer_id', String(activePeer.id));
        if (activePeer && lastMessageId > 0) url.searchParams.set('after_id', String(lastMessageId));
        const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'No se pudo actualizar el chat.');
        if (adminMode) renderUsers(data.users || []);
        if (data.peer) {
          if (!adminMode) activePeer = data.peer;
          if (!adminMode) {
            widget.querySelector('[data-chat-peer-title]').textContent = 'Administrador';
            const online = Boolean(data.admin_online);
            widget.querySelector('[data-chat-peer-status]').textContent = online ? 'En línea' : 'Desconectado';
            widget.querySelector('[data-chat-status]').textContent = online ? 'El administrador está disponible' : 'El administrador no está conectado';
            widget.querySelector('[data-chat-launcher-status]').classList.toggle('online', online);
          } else if (activePeer) {
            const currentUser = (data.users || []).find((user) => Number(user.id) === Number(activePeer.id));
            if (currentUser) activePeer = currentUser;
            widget.querySelector('[data-chat-peer-status]').textContent = supervisorMode ? 'Solo lectura · conversación supervisada' : (activePeer.online ? 'En línea' : 'Desconectado');
            widget.querySelector('[data-chat-status]').textContent = supervisorMode ? 'Modo supervisor: solo visualización' : (activePeer.online ? 'Usuario conectado' : 'Conversación guardada');
          }
          (data.messages || []).forEach(appendMessage);
          const typing = widget.querySelector('[data-chat-typing]');
          typing.hidden = !data.typing;
          if (data.typing) scrollToLatest();
        }
        const unreadTotal = adminMode
          ? (data.users || []).reduce((total, user) => total + user.unread_count, 0)
          : 0;
        const unreadBadge = widget.querySelector('[data-chat-unread]');
        if (unreadBadge) {
          unreadBadge.hidden = unreadTotal === 0;
          unreadBadge.textContent = String(unreadTotal);
        }
        showNotice('');
      } catch (error) {
        if (windowPanel && !windowPanel.hidden) showNotice(error.message || 'No se pudo actualizar el chat.');
      } finally {
        pollInProgress = false;
      }
    };

    const postChatAction = async (action, data = {}) => {
      const payload = new FormData();
      payload.set('csrf', token);
      payload.set('chat_action', action);
      if (adminMode && activePeer) payload.set('peer_id', String(activePeer.id));
      Object.entries(data).forEach(([key, value]) => payload.set(key, String(value)));
      const response = await fetch(endpoint, { method: 'POST', body: payload, credentials: 'same-origin', headers: { Accept: 'application/json' } });
      const result = await response.json();
      if (!response.ok) throw new Error(result.error || 'No se pudo enviar el mensaje.');
      return result;
    };

    const sendMessage = async (messageText = null) => {
      if (supervisorMode) {
        showNotice('El SuperAdmin solo puede visualizar las conversaciones.');
        return false;
      }
      if (adminMode && !activePeer) {
        showNotice('Selecciona un usuario antes de enviar un mensaje.');
        return false;
      }
      const text = messageText === null ? composer.value.trim() : messageText.trim();
      if (!text && !pendingFile) return false;
      if (text.length > 3000) {
        showNotice('El mensaje no puede superar los 3000 caracteres.');
        return false;
      }
      const payload = new FormData();
      payload.set('csrf', token);
      payload.set('chat_action', 'message');
      payload.set('message_text', text);
      if (adminMode && activePeer) payload.set('peer_id', String(activePeer.id));
      if (pendingFile) payload.set('attachment', pendingFile);
      const sendButton = widget.querySelector('.chat-send-button');
      sendButton.disabled = true;
      showNotice('');
      try {
        const response = await fetch(endpoint, { method: 'POST', body: payload, credentials: 'same-origin', headers: { Accept: 'application/json' } });
        const result = await response.json();
        if (!response.ok) throw new Error(result.error || 'No se pudo enviar el mensaje.');
        if (messageText === null) composer.value = '';
        pendingFile = null;
        if (attachment) attachment.value = '';
        const preview = widget.querySelector('[data-chat-attachment-preview]');
        preview.replaceChildren();
        preview.hidden = true;
        await pollState();
        composer.focus();
        return true;
      } catch (error) {
        showNotice(error.message || 'No se pudo enviar el mensaje.');
        return false;
      } finally {
        sendButton.disabled = false;
      }
    };

    if (adminMode && composer) {
      composer.disabled = true;
      const sendButton = widget.querySelector('.chat-send-button');
      if (sendButton) sendButton.disabled = true;
    }
    launcher?.addEventListener('click', () => {
      const open = windowPanel.hidden;
      setOpen(open);
      if (open) pollState().catch((error) => showNotice(error.message));
    });
    widget.querySelector('[data-chat-close]')?.addEventListener('click', () => setOpen(false));
    form?.addEventListener('submit', (event) => {
      event.preventDefault();
      sendMessage().catch((error) => showNotice(error.message));
    });
    composer?.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
        event.preventDefault();
        sendMessage().catch((error) => showNotice(error.message));
      }
    });
    composer?.addEventListener('input', () => {
      window.clearTimeout(typingTimer);
      typingTimer = window.setTimeout(() => {
        postChatAction('typing', { typing: composer.value.trim() !== '' })
          .catch((error) => showNotice(error.message));
      }, 350);
    });
    orderTemplateButton?.addEventListener('click', () => {
      if (!activePeer) {
        showNotice('Selecciona un usuario antes de preparar una respuesta.');
        return;
      }
      const customerName = activePeer.full_name || activePeer.username || '[Nombre del cliente]';
      const orderCode = activePeer.latest_order_code || '[código del pedido]';
      const orderStatus = activePeer.latest_order_status || '[estado del pedido]';
      composer.value = `¡Hola, ${customerName}! Gracias por escribir a GoStore.\n\nHemos recibido tu consulta sobre el pedido ${orderCode}. El estado actual de tu pedido es ${orderStatus}. El estado del pago es [confirmado / pendiente de verificación] y tu pedido se encuentra [en fase de empaque / estado actualizado].\n\nDetalles de la entrega: tu envío será despachado mediante nuestro servicio de delivery local. El tiempo estimado de llegada a tu dirección es [horas / rango de tiempo].\n\nSi deseas coordinar alguna referencia adicional para el repartidor, puedes responder por este chat o escribirnos directamente por WhatsApp Business al +${widget.dataset.adminPhone}. ¡Gracias por comprar local con GoStore!`;
      composer.focus();
      composer.setSelectionRange(composer.value.length, composer.value.length);
      showNotice('Revisa y completa los campos entre corchetes antes de enviar.');
    });
    composer?.addEventListener('blur', () => {
      window.clearTimeout(typingTimer);
      postChatAction('typing', { typing: false }).catch((error) => showNotice(error.message));
    });
    attachment?.addEventListener('change', () => {
      const file = attachment.files?.[0] || null;
      pendingFile = file;
      const preview = widget.querySelector('[data-chat-attachment-preview]');
      preview.replaceChildren();
      if (!file) {
        preview.hidden = true;
        return;
      }
      if (file.size > 20 * 1024 * 1024) {
        pendingFile = null;
        attachment.value = '';
        showNotice('El archivo no puede superar los 20 MB.');
        preview.hidden = true;
        return;
      }
      const label = document.createElement('span');
      label.textContent = `${file.name} · ${(file.size / (1024 * 1024)).toFixed(1)} MB`;
      const remove = document.createElement('button');
      remove.type = 'button';
      remove.textContent = '×';
      remove.setAttribute('aria-label', 'Quitar archivo adjunto');
      remove.addEventListener('click', () => {
        pendingFile = null;
        attachment.value = '';
        preview.hidden = true;
      });
      preview.append(label, remove);
      preview.hidden = false;
      showNotice('');
    });
    widget.querySelector('[data-chat-emoji-toggle]')?.addEventListener('click', () => {
      const picker = widget.querySelector('[data-chat-emoji-picker]');
      picker.hidden = !picker.hidden;
    });
    widget.querySelectorAll('[data-chat-emoji]').forEach((button) => {
      button.addEventListener('click', () => {
        const emoji = button.dataset.chatEmoji;
        const start = composer.selectionStart;
        const end = composer.selectionEnd;
        composer.setRangeText(emoji, start, end, 'end');
        composer.focus();
        widget.querySelector('[data-chat-emoji-picker]').hidden = true;
        composer.dispatchEvent(new Event('input', { bubbles: true }));
      });
    });
    document.addEventListener('gostore:send-chat-message', (event) => {
      setOpen(true);
      if (event.detail?.message) sendMessage(event.detail.message);
    });
    document.addEventListener('visibilitychange', pollState);
    window.setInterval(() => {
      if (!windowPanel.hidden || Date.now() - lastPollAt >= 20000) pollState();
    }, 2500);
    pollState();
    window.addEventListener('pagehide', () => {
      const payload = new FormData();
      payload.set('csrf', token);
      payload.set('chat_action', 'offline');
      navigator.sendBeacon(endpoint, payload);
    });
  }

  if (choice) {
    const closeChoice = () => {
      choice.hidden = true;
      sendConsultation = null;
    };
    document.querySelector('[data-consultation-choice]')?.addEventListener('click', (event) => {
      const button = event.currentTarget;
      const inquiryForm = button.closest('.product-inquiry-form');
      const message = inquiryForm.querySelector('textarea[name="message"]');
      if (!message.reportValidity()) return;
      const name = inquiryForm.dataset.chatProductName;
      const code = inquiryForm.dataset.chatProductCode;
      sendConsultation = `Consulta sobre ${name} (código ${code}):\n\n${message.value.trim()}`;
      choice.hidden = false;
      choice.querySelector('[data-chat-choice-send]').focus();
    });
    choice.querySelector('[data-chat-choice-close]')?.addEventListener('click', closeChoice);
    choice.addEventListener('click', (event) => {
      if (event.target === choice) closeChoice();
    });
    choice.querySelector('[data-chat-choice-send]')?.addEventListener('click', () => {
      if (!sendConsultation) return;
      document.dispatchEvent(new CustomEvent('gostore:send-chat-message', { detail: { message: sendConsultation } }));
      closeChoice();
    });
    choice.querySelector('[data-chat-choice-whatsapp]')?.addEventListener('click', () => {
      if (!sendConsultation) return;
      const userName = widget?.dataset.userName || document.querySelector('.nav-user b')?.textContent || '';
      const email = widget?.dataset.userEmail || '';
      const text = `${sendConsultation}\n\nCliente: ${userName}${email ? `\nCorreo: ${email}` : ''}`;
      const url = new URL(`https://wa.me/${choice.dataset.adminPhone}`);
      url.searchParams.set('text', text);
      window.open(url.toString(), '_blank', 'noopener');
      closeChoice();
    });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && !choice.hidden) closeChoice();
    });
  }
});
