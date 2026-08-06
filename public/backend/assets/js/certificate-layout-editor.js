/**
 * Vanilla JS Drag-and-Drop Engine for Certificate Variable Positioning.
 * Operates on percentage coordinate system (0-100% of an A4 canvas).
 */
document.addEventListener('DOMContentLoaded', () => {
    const config = window.CertLayoutConfig;
    if (!config) return;

    const layoutItems = new Map();
    let hasUnsavedChanges = false;
    
    function markUnsaved() {
        if (hasUnsavedChanges) return;
        hasUnsavedChanges = true;
        const badge = document.getElementById('unsaved-badge');
        if (badge) {
            badge.className = 'badge bg-warning text-dark px-2 py-1';
            badge.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Belum Disimpan';
        }
    }

    const container = document.getElementById('cert-canvas-container');
    const noSelectionNotice = document.getElementById('no-selection-notice');
    const inspectorForm = document.getElementById('inspector-form');
    const inspectorLabel = document.getElementById('inspector-var-label');

    // Inspector inputs
    const inputX = document.getElementById('prop-x');
    const inputY = document.getElementById('prop-y');
    const inputFontSize = document.getElementById('prop-fontSize');
    const selectFontWeight = document.getElementById('prop-fontWeight');
    const selectFontStyle = document.getElementById('prop-fontStyle');
    const radioAligns = document.getElementsByName('prop-textAlign');
    const colorPicker = document.getElementById('prop-color-picker');
    const colorHex = document.getElementById('prop-color-hex');

    // Action buttons & toggles
    const btnRemove = document.getElementById('btn-remove-element');
    const btnSave = document.getElementById('btn-save-layout');
    const btnReset = document.getElementById('btn-reset-layout');
    const toggleGrid = document.getElementById('toggle-grid');
    const gridLines = document.getElementById('canvas-grid-lines');

    // In-memory state of active layout items mapped by variable key
    let selectedKey = null;
    let isDragging = false;
    let dragOffsetX = 0;
    let dragOffsetY = 0;

    // Initialize layout state from server config
    function init() {
        const sourceData = config.existingLayout || config.defaultLayout;
        for (const [key, props] of Object.entries(sourceData)) {
            if (config.definitions[key]) {
                layoutItems.set(key, { ...props });
                renderCanvasItem(key, layoutItems.get(key));
            }
        }
        updatePaletteIndicators();
    }

    // Render an HTML item onto the A4 Canvas representing a variable
    function renderCanvasItem(key, props) {
        let el = document.getElementById(`canvas-item-${key}`);
        if (!el) {
            el = document.createElement('div');
            el.id = `canvas-item-${key}`;
            el.dataset.key = key;
            el.className = `position-absolute px-2 py-1 user-select-none text-nowrap cursor-pointer transition-none var-${key}`;
            
            // Dynamic anchor based on alignment to prevent ragged edges
            let anchorX = '-50%';
            if (props.textAlign === 'left') anchorX = '0%';
            else if (props.textAlign === 'right') anchorX = '-100%';
            
            el.style.transform = `translate(${anchorX}, -50%)`;
            el.style.border = '1px dashed transparent';
            el.style.borderRadius = '3px';
            el.style.lineHeight = '1.2';

            el.addEventListener('pointerdown', (e) => onPointerDown(e, key));
            container.appendChild(el);
        }

        const def = config.definitions[key] || {};
        const isImage = def.type === 'image';

        if (isImage) {
            el.classList.remove('text-nowrap');
            el.style.width = `${props.width || 14}%`;
            
            // Determine image source: use samples URL for any image variable
            const isSig = key === 'tanda_tangan';
            const imgUrl = isSig ? config.signatureUrl : (config.samples[key] || null);
            
            if (imgUrl) {
                const altText = def.label || key;
                el.innerHTML = `<img src="${imgUrl}" alt="${altText}" style="width: 100%; max-height: 150px; object-fit: contain; pointer-events: none; display: block; margin: 0 auto; border: 1px solid #ccc;">`;
            } else {
                const icon = isSig ? 'bi-vector-pen' : 'bi-person-bounding-box';
                const label = def.label || (isSig ? 'Tanda Tangan' : 'Gambar');
                el.innerHTML = `<div class="border border-secondary border-dashed px-2 py-2 text-center text-dark rounded shadow-sm" style="background: rgba(255, 255, 255, 0.92); pointer-events: none;"><i class="bi ${icon} text-primary fs-14 d-block mb-1"></i><span class="fw-bold fs-11 d-block">${label}</span><small class="text-muted fs-10 d-block">(Belum Diunggah)</small></div>`;
            }
        } else {
            const sampleText = config.samples[key] || key;
            el.innerText = sampleText;
            el.style.width = 'auto';
        }

        // Apply visual properties
        el.style.left = `${props.x}%`;
        el.style.top = `${props.y}%`;
        el.style.fontSize = `calc(${props.fontSize || 14} * 0.1188cqi)`; // exact match for pt on A4
        el.style.fontWeight = props.fontWeight || '400';
        el.style.fontStyle = props.fontStyle || 'normal';
        el.style.color = props.color || '#000000';
        el.style.textAlign = props.textAlign || 'center';

        // Highlight if active
        if (selectedKey === key) {
            el.style.border = '1px dashed #0d6efd';
            el.style.backgroundColor = 'rgba(13, 110, 253, 0.08)';
            el.style.zIndex = '10';
        } else {
            el.style.border = '1px dashed transparent';
            el.style.backgroundColor = 'transparent';
            el.style.zIndex = '1';
        }
    }

    // Palette Click Handling: Select or Instantiate Item onto Center Canvas
    document.getElementById('palette-list')?.addEventListener('click', (e) => {
        const item = e.target.closest('.palette-item');
        if (!item) return;

        const key = item.dataset.variableKey;
        if (!layoutItems.has(key)) {
            // Instantiate with safe fallback default values at canvas center (50%, 50%)
            const defaultProps = config.defaultLayout[key] || {
                x: 50, y: 50, fontSize: 14, fontWeight: '400',
                fontStyle: 'normal', textAlign: 'center', color: '#0F2A4A'
            };
            layoutItems.set(key, { ...defaultProps, x: 50, y: 50 });
            renderCanvasItem(key, layoutItems.get(key));
            updatePaletteIndicators();
            markUnsaved();
        }

        selectItem(key);
    });

    function selectItem(key) {
        selectedKey = key;
        layoutItems.forEach((_, k) => {
            const el = document.getElementById(`canvas-item-${k}`);
            if (el) {
                el.style.border = (k === key) ? '1px dashed #0d6efd' : '1px dashed transparent';
                el.style.backgroundColor = (k === key) ? 'rgba(13, 110, 253, 0.08)' : 'transparent';
                el.style.zIndex = (k === key) ? '10' : '1';
            }
        });

        updatePaletteIndicators();
        populateInspector();
    }

    function updatePaletteIndicators() {
        document.querySelectorAll('.palette-item').forEach((item) => {
            const key = item.dataset.variableKey;
            const badge = item.querySelector('.badge');
            if (layoutItems.has(key)) {
                badge.className = 'badge bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center';
                badge.innerHTML = '<i class="bi bi-check-lg fs-12"></i>';
            } else {
                badge.className = 'badge bg-light text-dark rounded-circle border p-2 d-flex align-items-center justify-content-center';
                badge.innerHTML = '<i class="bi bi-plus-lg fs-12"></i>';
            }
            item.classList.toggle('active', key === selectedKey);
        });
    }

    function populateInspector() {
        if (!selectedKey || !layoutItems.has(selectedKey)) {
            noSelectionNotice.classList.remove('d-none');
            inspectorForm.classList.add('d-none');
            return;
        }

        noSelectionNotice.classList.add('d-none');
        inspectorForm.classList.remove('d-none');

        const props = layoutItems.get(selectedKey);
        const def = config.definitions[selectedKey] || {};
        inspectorLabel.innerText = def.label || selectedKey;

        inputX.value = props.x ?? 50;
        inputY.value = props.y ?? 50;
        
        const coordDisplay = document.getElementById('coord-display');
        if (coordDisplay) {
            coordDisplay.innerText = `X: ${props.x ?? 50}% | Y: ${props.y ?? 50}%`;
        }

        const isImage = def.type === 'image';
        document.getElementById('text-properties').style.display = isImage ? 'none' : 'block';
        const imgProps = document.getElementById('image-properties');
        if (imgProps) imgProps.style.display = isImage ? 'block' : 'none';

        if (isImage) {
            const inputWidth = document.getElementById('prop-width');
            if (inputWidth) inputWidth.value = props.width ?? 14;
        } else {
            inputFontSize.value = props.fontSize ?? 14;
            selectFontWeight.value = props.fontWeight ?? '400';
            selectFontStyle.value = props.fontStyle ?? 'normal';

            for (const radio of radioAligns) {
                radio.checked = (radio.value === (props.textAlign || 'center'));
            }

            const col = props.color ?? '#000000';
            colorPicker.value = col;
            colorHex.value = col;
        }
    }

    // Bind Inspector Input Events to Live Canvas Render
    function updateSelectedProperty(prop, value) {
        if (!selectedKey || !layoutItems.has(selectedKey)) return;
        const item = layoutItems.get(selectedKey);
        item[prop] = value;
        renderCanvasItem(selectedKey, item);
        markUnsaved();
    }

    inputX?.addEventListener('input', (e) => updateSelectedProperty('x', parseFloat(e.target.value) || 0));
    inputY?.addEventListener('input', (e) => updateSelectedProperty('y', parseFloat(e.target.value) || 0));
    inputFontSize?.addEventListener('input', (e) => updateSelectedProperty('fontSize', parseInt(e.target.value, 10) || 12));
    
    const msWordSizes = [8, 9, 10, 11, 12, 14, 16, 18, 20, 22, 24, 26, 28, 36, 48, 72];

    document.getElementById('btn-fontsize-down')?.addEventListener('click', () => {
        if (!inputFontSize) return;
        const cur = parseInt(inputFontSize.value, 10) || 12;
        const prevSizes = msWordSizes.filter(s => s < cur);
        const val = prevSizes.length > 0 ? Math.max(...prevSizes) : Math.max(6, cur - 1);
        inputFontSize.value = val;
        updateSelectedProperty('fontSize', val);
    });

    document.getElementById('btn-fontsize-up')?.addEventListener('click', () => {
        if (!inputFontSize) return;
        const cur = parseInt(inputFontSize.value, 10) || 12;
        const nextSizes = msWordSizes.filter(s => s > cur);
        const val = nextSizes.length > 0 ? Math.min(...nextSizes) : Math.min(100, cur + 2);
        inputFontSize.value = val;
        updateSelectedProperty('fontSize', val);
    });


    document.getElementById('dropdown-fontsize-list')?.addEventListener('click', (e) => {
        const item = e.target.closest('[data-val]');
        if (!item || !inputFontSize) return;
        const val = parseInt(item.getAttribute('data-val'), 10) || 12;
        inputFontSize.value = val;
        updateSelectedProperty('fontSize', val);
    });

    selectFontWeight?.addEventListener('change', (e) => updateSelectedProperty('fontWeight', e.target.value));
    selectFontStyle?.addEventListener('change', (e) => updateSelectedProperty('fontStyle', e.target.value));

    const inputWidth = document.getElementById('prop-width');
    inputWidth?.addEventListener('input', (e) => updateSelectedProperty('width', parseFloat(e.target.value) || 14));

    // D-Pad Directional Navigation Handlers
    function nudgeSelected(dx, dy) {
        if (!selectedKey || !layoutItems.has(selectedKey)) return;
        const item = layoutItems.get(selectedKey);

        let step = 1.0;
        const checkedStep = document.querySelector('input[name="nudge-step"]:checked');
        if (checkedStep) step = parseFloat(checkedStep.value) || 1.0;

        let x = Math.max(0, Math.min(100, parseFloat((parseFloat(item.x) + (dx * step)).toFixed(1))));
        let y = Math.max(0, Math.min(100, parseFloat((parseFloat(item.y) + (dy * step)).toFixed(1))));

        item.x = x;
        item.y = y;
        renderCanvasItem(selectedKey, item);
        inputX.value = x;
        inputY.value = y;

        const coordDisplay = document.getElementById('coord-display');
        if (coordDisplay) {
            coordDisplay.innerText = `X: ${x}% | Y: ${y}%`;
        }
        markUnsaved();
    }

    document.getElementById('btn-nudge-up')?.addEventListener('click', () => nudgeSelected(0, -1));
    document.getElementById('btn-nudge-down')?.addEventListener('click', () => nudgeSelected(0, 1));
    document.getElementById('btn-nudge-left')?.addEventListener('click', () => nudgeSelected(-1, 0));
    document.getElementById('btn-nudge-right')?.addEventListener('click', () => nudgeSelected(1, 0));

    for (const radio of radioAligns) {
        radio.addEventListener('change', (e) => {
            if (e.target.checked) updateSelectedProperty('textAlign', e.target.value);
        });
    }

    colorPicker?.addEventListener('input', (e) => {
        colorHex.value = e.target.value.toUpperCase();
        updateSelectedProperty('color', e.target.value);
    });

    colorHex?.addEventListener('input', (e) => {
        let val = e.target.value;
        if (!val.startsWith('#')) val = '#' + val;
        if (/^#[0-9A-F]{6}$/i.test(val)) {
            colorPicker.value = val;
            updateSelectedProperty('color', val.toUpperCase());
        }
    });

    // Remove item from canvas
    btnRemove?.addEventListener('click', () => {
        if (!selectedKey) return;
        const el = document.getElementById(`canvas-item-${selectedKey}`);
        if (el) el.remove();
        layoutItems.delete(selectedKey);
        selectedKey = null;
        updatePaletteIndicators();
        populateInspector();
        markUnsaved();
    });

    // Drag and Drop Logic
    function onPointerDown(e, key) {
        if (e.button !== 0 && e.pointerType === 'mouse') return; // left click only
        e.stopPropagation();
        selectItem(key);

        const el = document.getElementById(`canvas-item-${key}`);
        const rect = el.getBoundingClientRect();
        const item = layoutItems.get(key) || {};

        let anchorPx = rect.left + rect.width / 2; // default center
        if (item.textAlign === 'left') anchorPx = rect.left;
        else if (item.textAlign === 'right') anchorPx = rect.right;

        isDragging = true;
        dragOffsetX = e.clientX - anchorPx;
        dragOffsetY = e.clientY - (rect.top + rect.height / 2);

        el.setPointerCapture(e.pointerId);
        el.addEventListener('pointermove', onPointerMove);
        el.addEventListener('pointerup', onPointerUp);
        el.addEventListener('pointercancel', onPointerUp);
    }

    function onPointerMove(e) {
        if (!isDragging || !selectedKey) return;

        const contRect = container.getBoundingClientRect();
        let clientX = e.clientX - dragOffsetX;
        let clientY = e.clientY - dragOffsetY;

        // Convert coordinates to percentage of container dimensions (0-100%)
        let x = ((clientX - contRect.left) / contRect.width) * 100;
        let y = ((clientY - contRect.top) / contRect.height) * 100;

        // Clamp values to canvas bounds
        x = Math.max(0, Math.min(100, parseFloat(x.toFixed(1))));
        y = Math.max(0, Math.min(100, parseFloat(y.toFixed(1))));

        const item = layoutItems.get(selectedKey);
        item.x = x;
        item.y = y;

        // Immediately update alignment anchor while dragging if properties change
        let anchorX = '-50%';
        if (item.textAlign === 'left') anchorX = '0%';
        else if (item.textAlign === 'right') anchorX = '-100%';
        
        const el = document.getElementById(`canvas-item-${selectedKey}`);
        if (el) el.style.transform = `translate(${anchorX}, -50%)`;

        renderCanvasItem(selectedKey, item);

        inputX.value = x;
        inputY.value = y;
        const coordDisplay = document.getElementById('coord-display');
        if (coordDisplay) coordDisplay.innerText = `X: ${x}% | Y: ${y}%`;
        
        markUnsaved();
    }

    function onPointerUp(e) {
        if (!isDragging) return;
        isDragging = false;
        const el = e.target;
        if (el && el.releasePointerCapture) {
            try { el.releasePointerCapture(e.pointerId); } catch (_) {}
            el.removeEventListener('pointermove', onPointerMove);
            el.removeEventListener('pointerup', onPointerUp);
            el.removeEventListener('pointercancel', onPointerUp);
        }
    }

    // Keyboard Nudging for Precise Alignment
    document.addEventListener('keydown', (e) => {
        if (!selectedKey || !layoutItems.has(selectedKey)) return;
        if (['INPUT', 'SELECT', 'TEXTAREA'].includes(document.activeElement?.tagName)) return;

        let step = e.shiftKey ? 1.0 : 0.2; // shift for larger steps
        const item = layoutItems.get(selectedKey);
        let handled = false;

        switch (e.key) {
            case 'ArrowLeft': item.x = Math.max(0, parseFloat((item.x - step).toFixed(1))); handled = true; break;
            case 'ArrowRight': item.x = Math.min(100, parseFloat((item.x + step).toFixed(1))); handled = true; break;
            case 'ArrowUp': item.y = Math.max(0, parseFloat((item.y - step).toFixed(1))); handled = true; break;
            case 'ArrowDown': item.y = Math.min(100, parseFloat((item.y + step).toFixed(1))); handled = true; break;
        }

        if (handled) {
            e.preventDefault();
            renderCanvasItem(selectedKey, item);
            inputX.value = item.x;
            inputY.value = item.y;
            const coordDisplay = document.getElementById('coord-display');
            if (coordDisplay) coordDisplay.innerText = `X: ${item.x}% | Y: ${item.y}%`;
            markUnsaved();
        }
    });

    // Grid Display Toggle
    toggleGrid?.addEventListener('change', (e) => {
        gridLines?.classList.toggle('d-none', !e.target.checked);
    });

    // Reset Default Button
    btnReset?.addEventListener('click', () => {
        if (!confirm('Apakah Anda yakin ingin mengatur ulang posisi ke standar bawaan sistem?')) return;

        layoutItems.forEach((_, key) => {
            const el = document.getElementById(`canvas-item-${key}`);
            if (el) el.remove();
        });
        layoutItems.clear();

        for (const [key, props] of Object.entries(config.defaultLayout)) {
            if (config.definitions[key]) {
                layoutItems.set(key, { ...props });
                renderCanvasItem(key, layoutItems.get(key));
            }
        }
        selectedKey = null;
        updatePaletteIndicators();
        populateInspector();
        markUnsaved();
    });

    // Save Layout Button via Form Submission
    btnSave?.addEventListener('click', () => {
        if (layoutItems.size === 0) {
            alert('Kanvas tidak boleh sepenuhnya kosong. Tempatkan minimal satu elemen variabel sertifikat.');
            return;
        }

        const payload = [];
        layoutItems.forEach((props, key) => {
            payload.push({
                key: key,
                x: props.x,
                y: props.y,
                width: props.width || null,
                fontSize: props.fontSize || null,
                fontWeight: props.fontWeight || null,
                fontStyle: props.fontStyle || null,
                textAlign: props.textAlign || null,
                color: props.color || null,
            });
        });

        // Submit via dynamically created hidden form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = config.updateUrl;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = config.csrfToken;
        form.appendChild(csrfInput);

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PUT';
        form.appendChild(methodInput);

        payload.forEach((item, index) => {
            for (const [propKey, propVal] of Object.entries(item)) {
                if (propVal !== null && propVal !== '') {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `variable_positions[${index}][${propKey}]`;
                    input.value = propVal;
                    form.appendChild(input);
                }
            }
        });

        document.body.appendChild(form);
        form.submit();
    });

    // Boot
    init();
});
