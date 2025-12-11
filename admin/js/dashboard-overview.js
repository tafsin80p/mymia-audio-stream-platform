(function () {
    var hasDashboardData = typeof window.nymiaDashboardData !== 'undefined';
    var deletedSearchInput = null;
    var deletedSearchField = null;
    var deletedFilterSelect = null;
    var deletedSearchDebounce = null;
    var deletedDataCache = null;
    var tabLoader = null;
    var tabLoaderTimer = null;

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    function hexToRgba(hex, alpha) {
        var cleaned = hex.replace('#', '').trim();
        if (cleaned.length === 3) {
            cleaned = cleaned.split('').map(function (c) { return c + c; }).join('');
        }
        var bigint = parseInt(cleaned, 16);
        var r = (bigint >> 16) & 255;
        var g = (bigint >> 8) & 255;
        var b = bigint & 255;
        return 'rgba(' + r + ',' + g + ',' + b + ',' + clamp(alpha, 0, 1) + ')';
    }

    function withAlpha(color, alpha) {
        if (!color) {
            return 'rgba(99,102,241,' + clamp(alpha, 0, 1) + ')';
        }

        if (color.startsWith('#')) {
            return hexToRgba(color, alpha);
        }

        if (color.startsWith('rgb(')) {
            var parts = color.replace('rgb(', '').replace(')', '').split(',');
            return 'rgba(' + parts[0].trim() + ',' + parts[1].trim() + ',' + parts[2].trim() + ',' + clamp(alpha, 0, 1) + ')';
        }

        if (color.startsWith('rgba(')) {
            var rgbaParts = color.replace('rgba(', '').replace(')', '').split(',');
            return 'rgba(' + rgbaParts[0].trim() + ',' + rgbaParts[1].trim() + ',' + rgbaParts[2].trim() + ',' + clamp(alpha, 0, 1) + ')';
        }

        return color;
    }

    function createGradient(ctx, color, opacity) {
        var gradient = ctx.createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, withAlpha(color, opacity));
        gradient.addColorStop(1, withAlpha(color, 0));
        return gradient;
    }

    function getColor(variable) {
        var el = document.documentElement;
        var styles = getComputedStyle(el);
        var value = styles.getPropertyValue(variable).trim();
        return value || '#6366f1';
    }

    function getProductStrings() {
        return (window.nymiaDashboardConfig && window.nymiaDashboardConfig.productStrings) || {};
    }

    function getTabLoaderElement() {
        if (!tabLoader) {
            tabLoader = document.getElementById('nymia-tab-loader');
        }
        return tabLoader;
    }

    function showTabLoader() {
        var loader = getTabLoaderElement();
        if (!loader || loader.classList.contains('is-visible')) {
            return;
        }
        loader.removeAttribute('hidden');
        loader.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(function () {
            loader.classList.add('is-visible');
        });
    }

    function hideTabLoader() {
        var loader = getTabLoaderElement();
        if (!loader) {
            return;
        }
        loader.classList.remove('is-visible');
        loader.setAttribute('aria-hidden', 'true');
        setTimeout(function () {
            if (loader && !loader.classList.contains('is-visible')) {
                loader.setAttribute('hidden', 'hidden');
            }
        }, 220);
    }

    function escapeHtml(str) {
        if (str === null || typeof str === 'undefined') {
            return '';
        }
        return String(str).replace(/[&<>"']/g, function (char) {
            var escapeMap = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            };
            return escapeMap[char] || char;
        });
    }

    function escapeAttr(str) {
        return escapeHtml(str).replace(/"/g, '&quot;');
    }

    function initSalesChart() {
        if (!hasDashboardData) {
            return;
        }
        var canvas = document.getElementById('nymia-sales-chart');
        if (!canvas) {
            return;
        }

        var ctx = canvas.getContext('2d');
        var accent = getColor('--nymia-accent') || 'rgba(99,102,241,1)';
        var success = getColor('--nymia-success') || 'rgba(52,211,153,1)';

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: nymiaDashboardData.sales_chart.labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Total Sales',
                        data: nymiaDashboardData.sales_chart.sales,
                        backgroundColor: createGradient(ctx, accent, 0.22),
                        borderRadius: 12,
                        borderSkipped: false,
                        borderWidth: 1,
                        borderColor: accent,
                    },
                    {
                        type: 'line',
                        label: 'Platform Earnings',
                        data: nymiaDashboardData.sales_chart.earnings,
                        borderColor: success,
                        tension: 0.4,
                        fill: false,
                        pointBackgroundColor: success,
                        pointRadius: 3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#cbd5f5',
                            usePointStyle: true,
                        },
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        borderColor: 'rgba(99,102,241,0.35)',
                        borderWidth: 1,
                        titleColor: '#f8fafc',
                        bodyColor: '#e2e8f0',
                        padding: 12,
                        cornerRadius: 12,
                    },
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#94a3b8',
                        },
                        grid: {
                            display: false,
                        },
                    },
                    y: {
                        ticks: {
                            color: '#64748b',
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.12)',
                        },
                    },
                },
            },
        });
    }

    function initUsersChart() {
        if (!hasDashboardData) {
            return;
        }
        var canvas = document.getElementById('nymia-users-chart');
        if (!canvas) {
            return;
        }

        var ctx = canvas.getContext('2d');
        var accent = getColor('--nymia-accent') || 'rgba(99,102,241,1)';
        var info = getColor('--nymia-info') || 'rgba(56,189,248,1)';

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: nymiaDashboardData.user_chart.labels,
                datasets: [
                    {
                        label: 'Creators',
                        data: nymiaDashboardData.user_chart.creators,
                        borderColor: accent,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: accent,
                        pointRadius: 3,
                    },
                    {
                        label: 'Buyers',
                        data: nymiaDashboardData.user_chart.buyers,
                        borderColor: info,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: info,
                        pointRadius: 3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#cbd5f5',
                            usePointStyle: true,
                        },
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        borderColor: 'rgba(99,102,241,0.35)',
                        borderWidth: 1,
                        titleColor: '#f8fafc',
                        bodyColor: '#e2e8f0',
                        padding: 12,
                        cornerRadius: 12,
                    },
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#94a3b8',
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.12)',
                        },
                    },
                    y: {
                        ticks: {
                            color: '#64748b',
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.12)',
                        },
                    },
                },
            },
        });
    }

    function attachRefreshHandler() {
        var refreshBtn = document.getElementById('nymia-refresh-dashboard');
        if (!refreshBtn) {
            return;
        }

        refreshBtn.addEventListener('click', function () {
            refreshBtn.classList.add('is-spinning');
            refreshBtn.disabled = true;

            setTimeout(function () {
                refreshBtn.classList.remove('is-spinning');
                refreshBtn.disabled = false;
            }, 1200);
        });
    }

    function initModuleTabs() {
        var tabButtons = document.querySelectorAll('.nymia-module-tab-button');
        if (!tabButtons.length) {
            return;
        }

        var panels = document.querySelectorAll('.nymia-module-panel');
        var transitionDuration = 320;

        tabButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var targetId = button.getAttribute('data-nymia-tab-target');
                var targetPanel = document.getElementById(targetId);
                if (!targetPanel || button.classList.contains('is-active')) {
                    return;
                }

                showTabLoader();
                if (tabLoaderTimer) {
                    clearTimeout(tabLoaderTimer);
                }

                tabButtons.forEach(function (btn) {
                    btn.classList.remove('is-active');
                    btn.setAttribute('aria-selected', 'false');
                });

                button.classList.add('is-active');
                button.setAttribute('aria-selected', 'true');

                panels.forEach(function (panel) {
                    if (panel === targetPanel) {
                        return;
                    }

                    if (!panel.hasAttribute('hidden')) {
                        panel.classList.remove('is-active');
                        panel.setAttribute('aria-hidden', 'true');
                        setTimeout(function () {
                            if (!panel.classList.contains('is-active')) {
                                panel.setAttribute('hidden', 'hidden');
                            }
                        }, transitionDuration);
                    } else {
                        panel.classList.remove('is-active');
                        panel.setAttribute('aria-hidden', 'true');
                    }
                });

                targetPanel.removeAttribute('hidden');
                targetPanel.setAttribute('aria-hidden', 'false');

                requestAnimationFrame(function () {
                    targetPanel.classList.add('is-active');
                });

                tabLoaderTimer = setTimeout(function () {
                    hideTabLoader();
                }, transitionDuration + 120);
            });
        });
    }

    function formatPendingLabel(count) {
        if (typeof window.nymiaDashboardConfig === 'undefined' || !window.nymiaDashboardConfig.strings) {
            return count + ' pending';
        }
        if (count === 0) {
            return window.nymiaDashboardConfig.strings.pendingEmpty || '0 pending';
        }
        var template = count === 1 ? window.nymiaDashboardConfig.strings.pendingSingle : window.nymiaDashboardConfig.strings.pendingPlural;
        if (!template) {
            return count + ' pending';
        }
        return template.replace('%d', count);
    }

    function initKycActions() {
        if (typeof window.nymiaDashboardConfig === 'undefined') {
            return;
        }

        var reviewContainer = document.querySelector('.nymia-kyc-review');
        if (!reviewContainer) {
            return;
        }

        var badge = reviewContainer.querySelector('.nymia-card-head .nymia-pill');

        reviewContainer.addEventListener('click', function (event) {
            var actionBtn = event.target.closest('[data-kyc-action]');
            if (!actionBtn) {
                return;
            }

            var decision = actionBtn.getAttribute('data-kyc-action');
            var userId = actionBtn.getAttribute('data-kyc-user');
            if (!decision || !userId) {
                return;
            }

            var card = actionBtn.closest('.nymia-kyc-card');
            var feedback = card ? card.querySelector('.nymia-kyc-feedback') : null;
            var statusPill = card ? card.querySelector('.nymia-kyc-status') : null;
            var otherButtons = card ? card.querySelectorAll('[data-kyc-action]') : [];

            otherButtons.forEach(function (btn) {
                btn.disabled = true;
            });

            if (feedback) {
                feedback.textContent = window.nymiaDashboardConfig.strings.processing || 'Processing...';
                feedback.classList.remove('is-success', 'is-error');
            }

            var formData = new FormData();
            formData.append('action', 'nymia_admin_review_kyc');
            formData.append('nonce', window.nymiaDashboardConfig.nonce);
            formData.append('userId', userId);
            formData.append('decision', decision);

            fetch(window.nymiaDashboardConfig.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData,
            })
                .then(function (response) { return response.json(); })
                .then(function (payload) {
                    otherButtons.forEach(function (btn) {
                        btn.disabled = false;
                    });

                    if (!payload || !payload.success) {
                        if (feedback) {
                            var errorMessage = (payload && payload.data && payload.data.message) || window.nymiaDashboardConfig.strings.error || 'Request failed.';
                            feedback.textContent = errorMessage;
                            feedback.classList.add('is-error');
                        }
                        return;
                    }

                    if (feedback) {
                        feedback.textContent = payload.data.message || '';
                        feedback.classList.add('is-success');
                    }

                    if (badge && typeof payload.data.pendingCount !== 'undefined') {
                        var pendingCount = parseInt(payload.data.pendingCount, 10);
                        badge.textContent = formatPendingLabel(pendingCount);
                        badge.classList.remove('nymia-pill-warning', 'nymia-pill-success');
                        badge.classList.add(pendingCount > 0 ? 'nymia-pill-warning' : 'nymia-pill-success');
                    }

                    if (card) {
                        card.classList.remove('is-approved', 'is-rejected');
                        card.classList.add(payload.data.status === 'approve' ? 'is-approved' : 'is-rejected');

                        if (statusPill) {
                            statusPill.textContent = payload.data.statusLabel || '';
                            statusPill.classList.remove('nymia-pill-warning');
                            if (payload.data.status === 'approve') {
                                statusPill.classList.add('nymia-pill-success');
                                statusPill.classList.remove('nymia-pill-danger');
                            } else {
                                statusPill.classList.add('nymia-pill-danger');
                                statusPill.classList.remove('nymia-pill-success');
                            }
                        }

                        setTimeout(function () {
                            card.style.opacity = '0';
                            card.style.transform = 'translateY(-6px)';
                            setTimeout(function () {
                                if (card.parentNode) {
                                    card.parentNode.removeChild(card);
                                }

                                if (!reviewContainer.querySelector('.nymia-kyc-card')) {
                                    var emptyState = reviewContainer.querySelector('.nymia-kyc-empty');
                                    if (!emptyState) {
                                        emptyState = document.createElement('div');
                                        emptyState.className = 'nymia-kyc-empty';
                                        emptyState.textContent = window.nymiaDashboardConfig.strings.empty || 'No pending KYC verifications.';
                                        reviewContainer.appendChild(emptyState);
                                    }
                                }
                            }, 260);
                        }, 650);
                    }
                })
                .catch(function () {
                    otherButtons.forEach(function (btn) {
                        btn.disabled = false;
                    });
                    if (feedback) {
                        feedback.textContent = window.nymiaDashboardConfig.strings.error || 'Something went wrong.';
                        feedback.classList.add('is-error');
                    }
                });
        });
    }

    function getActionMessages() {
        if (typeof window.nymiaDashboardConfig === 'undefined') {
            return {};
        }
        return window.nymiaDashboardConfig.userActions || {};
    }

    function getConfirmMessage(action, name) {
        var messages = getActionMessages();
        var keyMap = {
            suspend: 'confirmSuspend',
            ban: 'confirmBan',
            activate: 'confirmActivate',
            delete: 'confirmDelete'
        };
        var key = keyMap[action];
        var template = key ? messages[key] : '';
        if (!template) {
            return '';
        }
        return template.replace('%s', name || '');
    }

    function getActionTitle(action) {
        var messages = getActionMessages();
        var titleMap = {
            suspend: messages.titleSuspend || 'Suspend Account',
            ban: messages.titleBan || 'Ban Account',
            activate: messages.titleActivate || 'Activate Account',
            delete: messages.titleDelete || 'Delete Account'
        };
        return titleMap[action] || titleMap.suspend;
    }

    function getActionWarning(action, name) {
        var messages = getActionMessages();
        var warningMap = {
            ban: messages.warningBan || '',
            delete: messages.warningDelete || ''
        };
        var template = warningMap[action];
        return template ? template.replace('%s', name || '') : '';
    }

    function getConfirmLabel(action) {
        var messages = getActionMessages();
        var labelMap = {
            suspend: messages.confirmLabelSuspend || 'Suspend',
            ban: messages.confirmLabelBan || 'Ban',
            activate: messages.confirmLabelActivate || 'Activate',
            delete: messages.confirmLabelDelete || 'Delete'
        };
        return labelMap[action] || 'Confirm';
    }

    var dialogCache = null;
    var dialogKeydownHandler = null;

    function ensureDialog() {
        if (dialogCache) {
            return dialogCache;
        }

        var overlay = document.getElementById('nymia-admin-dialog');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'nymia-admin-dialog';
            overlay.className = 'nymia-admin-dialog';
            overlay.setAttribute('role', 'dialog');
            overlay.setAttribute('aria-modal', 'true');
            overlay.setAttribute('aria-hidden', 'true');
            overlay.setAttribute('hidden', 'hidden');
            overlay.innerHTML = '' +
                '<div class="nymia-admin-dialog-card">' +
                '  <div>' +
                '    <h3 data-dialog-title>Confirm Action</h3>' +
                '    <p data-dialog-message>Are you sure you want to continue?</p>' +
                '  </div>' +
                '  <div class="nymia-admin-dialog-note" data-dialog-note-wrapper>' +
                '    <label data-dialog-note-label for="nymia-dialog-note"></label>' +
                '    <textarea id="nymia-dialog-note" data-dialog-note rows="4" placeholder=""></textarea>' +
                '    <span class="nymia-dialog-note-hint" data-dialog-note-hint></span>' +
                '  </div>' +
                '  <div class="nymia-admin-dialog-meta">' +
                '    <span data-dialog-user></span>' +
                '    <span data-dialog-warning></span>' +
                '  </div>' +
                '  <div class="nymia-admin-dialog-actions">' +
                '    <button type="button" class="nymia-dialog-btn-cancel" data-dialog-cancel>Cancel</button>' +
                '    <button type="button" class="nymia-dialog-btn-confirm" data-dialog-confirm>Confirm</button>' +
                '  </div>' +
                '</div>';
            document.body.appendChild(overlay);
        }

        var card = overlay.querySelector('.nymia-admin-dialog-card');
        var titleEl = overlay.querySelector('[data-dialog-title]');
        var messageEl = overlay.querySelector('[data-dialog-message]');
        var userEl = overlay.querySelector('[data-dialog-user]');
        var warningEl = overlay.querySelector('[data-dialog-warning]');
        var cancelBtn = overlay.querySelector('[data-dialog-cancel]');
        var confirmBtn = overlay.querySelector('[data-dialog-confirm]');
        var noteWrapper = overlay.querySelector('[data-dialog-note-wrapper]');
        var noteLabel = overlay.querySelector('[data-dialog-note-label]');
        var noteField = overlay.querySelector('[data-dialog-note]');
        var noteHint = overlay.querySelector('[data-dialog-note-hint]');
        var durationWrapper = overlay.querySelector('[data-dialog-duration-wrapper]');
        var durationLabel = overlay.querySelector('[data-dialog-duration-label]');
        var durationValue = overlay.querySelector('[data-dialog-duration-value]');
        var durationUnit = overlay.querySelector('[data-dialog-duration-unit]');
        var durationHint = overlay.querySelector('[data-dialog-duration-hint]');

        dialogCache = {
            overlay: overlay,
            card: card,
            titleEl: titleEl,
            messageEl: messageEl,
            userEl: userEl,
            warningEl: warningEl,
            cancelBtn: cancelBtn,
            confirmBtn: confirmBtn,
            noteWrapper: noteWrapper,
            noteLabel: noteLabel,
            noteField: noteField,
            noteHint: noteHint,
            durationWrapper: durationWrapper,
            durationLabel: durationLabel,
            durationValue: durationValue,
            durationUnit: durationUnit,
            durationHint: durationHint
        };

        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) {
                hideDialog();
            }
        });

        cancelBtn.addEventListener('click', function () {
            hideDialog();
        });

        return dialogCache;
    }

    function handleDialogKeydown(event) {
        if (event.key === 'Escape') {
            hideDialog();
        }
    }

    function showDialog(config) {
        var dialog = ensureDialog();

        dialog.titleEl.textContent = config.title || 'Confirm Action';
        dialog.messageEl.textContent = config.message || '';
        dialog.userEl.textContent = config.userLabel || '';
        dialog.userEl.style.display = config.userLabel ? '' : 'none';
        dialog.warningEl.textContent = config.warning || '';
        dialog.warningEl.style.display = config.warning ? '' : 'none';

        dialog.confirmBtn.textContent = config.confirmLabel || 'Confirm';
        dialog.confirmBtn.classList.remove('nymia-dialog-btn-danger', 'nymia-dialog-btn-warning', 'nymia-dialog-btn-muted');
        if (config.confirmStyle === 'danger') {
            dialog.confirmBtn.classList.add('nymia-dialog-btn-danger');
        } else if (config.confirmStyle === 'warning') {
            dialog.confirmBtn.classList.add('nymia-dialog-btn-warning');
        } else if (config.confirmStyle === 'muted') {
            dialog.confirmBtn.classList.add('nymia-dialog-btn-muted');
        }

        dialog.confirmBtn.disabled = false;
        dialog.confirmBtn.onclick = function () {
            if (dialog.noteHint) {
                dialog.noteHint.textContent = '';
                dialog.noteHint.style.display = 'none';
            }

            var noteValue = '';
            if (config.noteEnabled && dialog.noteField) {
                noteValue = dialog.noteField.value.trim();
                if (config.noteRequired && !noteValue) {
                    if (dialog.noteHint) {
                        dialog.noteHint.textContent = config.noteRequiredMessage || 'Please provide a note.';
                        dialog.noteHint.style.display = 'block';
                    }
                    dialog.noteField.focus();
                    return;
                }
            }

            var durationPayload = null;
            if (config.durationEnabled && dialog.durationValue && dialog.durationUnit) {
                var rawDuration = parseInt(dialog.durationValue.value, 10);
                if (!isNaN(rawDuration) && rawDuration > 0) {
                    durationPayload = {
                        value: rawDuration,
                        unit: dialog.durationUnit.value
                    };
                }
            }

            hideDialog();

            if (typeof config.onConfirm === 'function') {
                config.onConfirm(noteValue, durationPayload);
            }
        };

        if (dialog.noteWrapper) {
            if (config.noteEnabled) {
                dialog.noteWrapper.classList.add('is-visible');
                dialog.noteWrapper.style.display = 'flex';
                if (dialog.noteLabel) {
                    dialog.noteLabel.textContent = config.noteLabel || 'Optional note to include in the email';
                }
                if (dialog.noteField) {
                    dialog.noteField.value = config.noteValue || '';
                    dialog.noteField.placeholder = config.notePlaceholder || '';
                    if (config.noteRequired) {
                        setTimeout(function () {
                            dialog.noteField.focus();
                        }, 60);
                    }
                }
                if (dialog.noteHint) {
                    dialog.noteHint.textContent = config.noteHint || '';
                    dialog.noteHint.style.display = config.noteHint ? 'block' : 'none';
                }
            } else {
                dialog.noteWrapper.classList.remove('is-visible');
                dialog.noteWrapper.style.display = 'none';
            }
        }

        if (dialog.durationWrapper) {
            if (config.durationEnabled) {
                dialog.durationWrapper.classList.add('is-visible');
                dialog.durationWrapper.style.display = 'flex';
                if (dialog.durationLabel) {
                    dialog.durationLabel.textContent = config.durationLabel || 'Suspend for';
                }
                if (dialog.durationValue) {
                    dialog.durationValue.value = config.durationValue || '';
                    dialog.durationValue.setAttribute('min', '1');
                }
                if (dialog.durationUnit) {
                    var unitOptions = config.durationUnits || [
                        { value: 'days', label: 'Days' },
                        { value: 'weeks', label: 'Weeks' },
                        { value: 'months', label: 'Months' },
                        { value: 'years', label: 'Years' }
                    ];
                    dialog.durationUnit.innerHTML = '';
                    unitOptions.forEach(function (opt) {
                        var option = document.createElement('option');
                        option.value = opt.value;
                        option.textContent = opt.label;
                        dialog.durationUnit.appendChild(option);
                    });
                    dialog.durationUnit.value = config.durationDefaultUnit || unitOptions[0].value;
                }
                if (dialog.durationHint) {
                    dialog.durationHint.textContent = config.durationHint || '';
                    dialog.durationHint.style.display = config.durationHint ? 'block' : 'none';
                }
            } else {
                dialog.durationWrapper.classList.remove('is-visible');
                dialog.durationWrapper.style.display = 'none';
            }
        }

        dialog.overlay.removeAttribute('hidden');
        dialog.overlay.setAttribute('aria-hidden', 'false');
        dialog.overlay.classList.add('is-visible');

        dialogKeydownHandler = handleDialogKeydown;
        document.addEventListener('keydown', dialogKeydownHandler);
    }

    function hideDialog() {
        if (!dialogCache) {
            return;
        }
        dialogCache.overlay.classList.remove('is-visible');
        dialogCache.overlay.setAttribute('aria-hidden', 'true');
        dialogCache.overlay.setAttribute('hidden', 'hidden');
        if (dialogCache.noteField) {
            dialogCache.noteField.value = '';
        }
        if (dialogCache.noteHint) {
            dialogCache.noteHint.textContent = '';
            dialogCache.noteHint.style.display = 'none';
        }
        if (dialogCache.noteWrapper) {
            dialogCache.noteWrapper.classList.remove('is-visible');
            dialogCache.noteWrapper.style.display = 'none';
        }
        if (dialogCache.durationValue) {
            dialogCache.durationValue.value = '';
        }
        if (dialogCache.durationHint) {
            dialogCache.durationHint.textContent = '';
            dialogCache.durationHint.style.display = 'none';
        }
        if (dialogCache.durationWrapper) {
            dialogCache.durationWrapper.classList.remove('is-visible');
            dialogCache.durationWrapper.style.display = 'none';
        }

        if (dialogKeydownHandler) {
            document.removeEventListener('keydown', dialogKeydownHandler);
            dialogKeydownHandler = null;
        }
    }

    function updateActionButtons(row, status) {
        var canStatus = row.getAttribute('data-can-status') === '1';
        var canDelete = row.getAttribute('data-can-delete') === '1';
        var isSelf = row.getAttribute('data-is-self') === '1';
        var buttons = row.querySelectorAll('[data-user-action]');

        buttons.forEach(function (button) {
            var actionType = button.getAttribute('data-user-action');
            if (isSelf) {
                button.style.display = 'none';
                return;
            }
            if (actionType === 'delete' && !canDelete) {
                button.style.display = 'none';
                return;
            }
            if (actionType !== 'delete' && !canStatus) {
                button.style.display = 'none';
                return;
            }
            var allowed = button.getAttribute('data-visible-for') || '';
            if (!allowed) {
                button.style.display = '';
                return;
            }

            var statuses = allowed.split(',').map(function (item) {
                return item.trim();
            }).filter(Boolean);

            button.style.display = statuses.indexOf(status) !== -1 ? '' : 'none';
        });
    }

    function initUserActions() {
        if (typeof window.nymiaDashboardConfig === 'undefined') {
            return;
        }

        var table = document.querySelector('.nymia-table');
        if (!table) {
            return;
        }

        var rows = table.querySelectorAll('tbody tr[data-user-status]');
        rows.forEach(function (row) {
            updateActionButtons(row, row.getAttribute('data-user-status'));
        });

        table.addEventListener('click', function (event) {
            var button = event.target.closest('[data-user-action]');
            if (!button) {
                return;
            }

            if (button.disabled || button.classList.contains('is-loading')) {
                return;
            }

            var action = button.getAttribute('data-user-action');
            var userId = button.getAttribute('data-user-id');
            var userName = button.getAttribute('data-user-name') || '';
            var row = button.closest('tr[data-user-id]');
            if (!action || !userId || !row) {
                return;
            }

            var confirmMessage = getConfirmMessage(action, userName);
            var noteConfig = getActionMessages();
            var placeholderMap = {
                suspend: noteConfig.notePlaceholderSuspend || '',
                ban: noteConfig.notePlaceholderBan || '',
                delete: noteConfig.notePlaceholderDelete || '',
                activate: noteConfig.notePlaceholderActivate || ''
            };
            var noteHints = {
                suspend: noteConfig.noteHintSuspend || '',
                ban: noteConfig.noteHintBan || '',
                delete: noteConfig.noteHintDelete || '',
                activate: noteConfig.noteHintActivate || ''
            };
            var requiresNote = action === 'ban' || action === 'delete';
            var enableNote = action !== 'activate';

            var ajaxAction = action === 'delete' ? 'nymia_admin_delete_user' : 'nymia_admin_update_user_status';
            var statusMap = {
                activate: 'active',
                suspend: 'suspended',
                ban: 'banned'
            };

            var performAction = function (noteValue, durationData) {
                var formData = new FormData();
                formData.append('action', ajaxAction);
                formData.append('nonce', window.nymiaDashboardConfig.userActionNonce || '');
                formData.append('userId', userId);

                if (action !== 'delete') {
                    formData.append('status', statusMap[action] || 'active');
                }
                if (noteValue) {
                    formData.append('note', noteValue);
                }
                if (action === 'suspend' && durationData && durationData.value && durationData.unit) {
                    formData.append('durationValue', durationData.value);
                    formData.append('durationUnit', durationData.unit);
                }

                button.classList.add('is-loading');
                button.disabled = true;

                fetch(window.nymiaDashboardConfig.ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                })
                    .then(function (response) { return response.json(); })
                    .then(function (payload) {
                        if (!payload || !payload.success) {
                            var errorMessage = (payload && payload.data && payload.data.message) || getActionMessages().errorGeneric || 'Unable to process request.';
                            window.alert(errorMessage);
                            return;
                        }

                        if (ajaxAction === 'nymia_admin_delete_user') {
                            row.style.opacity = '0.3';
                            setTimeout(function () {
                                if (row.parentNode) {
                                    row.parentNode.removeChild(row);
                                }
                            }, 220);
                        return;
                        }

                        var status = payload.data.status || 'active';
                        var label = payload.data.statusLabel || status;
                        row.setAttribute('data-user-status', status);

                        var pill = row.querySelector('[data-user-status-label]');
                        if (pill) {
                            pill.textContent = label;
                            pill.classList.remove('is-active', 'is-suspended', 'is-banned');
                            pill.classList.add('is-' + status);
                        }

                        updateActionButtons(row, status);
                    })
                    .catch(function () {
                        var errorMessage = getActionMessages().errorGeneric || 'Unable to process request.';
                        window.alert(errorMessage);
                    })
                    .finally(function () {
                        button.classList.remove('is-loading');
                        button.disabled = false;
                    });
            };

            showDialog({
                title: getActionTitle(action),
                message: confirmMessage || getActionMessages().fallbackMessage || 'Are you sure you want to proceed?',
                userLabel: userName ? userName : '',
                warning: getActionWarning(action, userName),
                confirmLabel: getConfirmLabel(action),
                confirmStyle: action === 'ban' || action === 'delete' ? 'danger' : (action === 'suspend' ? 'warning' : ''),
                noteEnabled: enableNote,
                noteRequired: requiresNote,
                noteLabel: noteConfig.noteLabel || 'Add a note for the user (sent via email)',
                notePlaceholder: placeholderMap[action] || noteConfig.notePlaceholder || '',
                noteHint: noteHints[action] || noteConfig.noteHint || '',
                noteRequiredMessage: noteConfig.noteRequired || 'Please provide a note before continuing.',
                durationEnabled: action === 'suspend',
                durationLabel: noteConfig.durationLabelSuspend || noteConfig.durationLabel || 'Suspend for',
                durationUnits: noteConfig.durationUnits || [],
                durationDefaultUnit: noteConfig.durationDefaultUnit || (noteConfig.durationUnits && noteConfig.durationUnits[0] ? noteConfig.durationUnits[0].value : 'days'),
                durationHint: noteConfig.durationHintSuspend || noteConfig.durationHint || '',
                onConfirm: performAction
            });
        });
    }

    function initUserSearch() {
        if (typeof window.nymiaDashboardConfig === 'undefined') {
            return;
        }

        var input = document.getElementById('nymia-user-search-input');
        var field = document.getElementById('nymia-user-search-field');
        var tableBody = document.querySelector('.nymia-table tbody');
        var roleSelect = document.getElementById('nymia-user-filter');

        if (!input || !field || !tableBody || !roleSelect) {
            return;
        }

        if (input.getAttribute('data-enhanced') === '1') {
            return;
        }
        input.setAttribute('data-enhanced', '1');

        var searchStrings = window.nymiaDashboardConfig.searchStrings || {};
        var lastTerm = null;
        var lastRole = roleSelect.value || 'all';
        var debounceTimer = null;

        if (searchStrings.placeholder) {
            input.setAttribute('placeholder', searchStrings.placeholder);
        }

        function setLoading(state) {
            field.classList.toggle('is-loading', !!state);
        }

        function updateClearButton() {
            if (input.value.trim()) {
                field.classList.add('has-value');
            } else {
                field.classList.remove('has-value');
            }
        }

        function renderUsers(users) {
            if (!users || !users.length) {
                var emptyText = searchStrings.empty || 'No users found.';
                tableBody.innerHTML = '<tr class="nymia-users-empty"><td colspan="6">' + escapeHtml(emptyText) + '</td></tr>';
                return;
            }

            var viewLabel = searchStrings.view || 'View';
            var manageLabel = searchStrings.manage || 'Manage';
            var suspendLabel = searchStrings.suspend || 'Suspend';
            var banLabel = searchStrings.ban || 'Ban';
            var activateLabel = searchStrings.activate || 'Activate';
            var deleteLabel = searchStrings.delete || 'Delete';

            var rowsHtml = users.map(function (user) {
                var userId = typeof user.user_id !== 'undefined' ? user.user_id : '';
                var actions = [];

                if (user.profile_url) {
                    actions.push(
                        '<a class="nymia-link-btn" href="' + escapeAttr(user.profile_url) + '" target="_blank" rel="noopener">' +
                        escapeHtml(viewLabel) +
                        '</a>'
                    );
                }

                if (user.can_edit && user.admin_url) {
                    actions.push(
                        '<a class="nymia-link-btn" href="' + escapeAttr(user.admin_url) + '" target="_blank" rel="noopener">' +
                        escapeHtml(manageLabel) +
                        '</a>'
                    );
                }

                if (user.can_manage_status) {
                    actions.push(
                        '<button type="button" class="nymia-chip-btn is-warning" data-user-action="suspend" data-visible-for="active" data-user-id="' + escapeAttr(userId) + '" data-user-name="' + escapeAttr(user.name || '') + '">' +
                        escapeHtml(suspendLabel) +
                        '</button>'
                    );
                    actions.push(
                        '<button type="button" class="nymia-chip-btn is-danger" data-user-action="ban" data-visible-for="active,suspended" data-user-id="' + escapeAttr(userId) + '" data-user-name="' + escapeAttr(user.name || '') + '">' +
                        escapeHtml(banLabel) +
                        '</button>'
                    );
                    actions.push(
                        '<button type="button" class="nymia-chip-btn is-success" data-user-action="activate" data-visible-for="suspended,banned" data-user-id="' + escapeAttr(userId) + '" data-user-name="' + escapeAttr(user.name || '') + '">' +
                        escapeHtml(activateLabel) +
                        '</button>'
                    );
                }

                if (user.can_delete) {
                    actions.push(
                        '<button type="button" class="nymia-chip-btn is-muted" data-user-action="delete" data-visible-for="active,suspended,banned" data-user-id="' + escapeAttr(userId) + '" data-user-name="' + escapeAttr(user.name || '') + '">' +
                        escapeHtml(deleteLabel) +
                        '</button>'
                    );
                }

                var avatarHtml = user.avatar
                    ? '<span class="nymia-avatar" style="background-image:url(' + escapeAttr(user.avatar) + ');"></span>'
                    : '<span class="nymia-avatar"></span>';

                var roleHtml = escapeHtml(user.role || '');
                var statusSlug = escapeAttr(user.status_slug || 'active');
                var statusLabel = escapeHtml(user.status || '');
                var joined = escapeHtml(user.joined || '');
                var earnings = escapeHtml(user.earnings || '-');
                var name = escapeHtml(user.name || '');

                return '' +
                    '<tr data-user-id="' + escapeAttr(userId) + '" data-user-status="' + statusSlug + '" data-is-self="' + (user.is_self ? '1' : '0') + '" data-can-status="' + (user.can_manage_status ? '1' : '0') + '" data-can-delete="' + (user.can_delete ? '1' : '0') + '">' +
                    '<td><div class="nymia-user-cell">' + avatarHtml + '<div><strong>' + name + '</strong><span>' + roleHtml + '</span></div></div></td>' +
                    '<td>' + roleHtml + '</td>' +
                    '<td><span class="nymia-status-pill is-' + statusSlug + '" data-user-status-label>' + statusLabel + '</span></td>' +
                    '<td>' + joined + '</td>' +
                    '<td>' + earnings + '</td>' +
                    '<td><div class="nymia-user-actions">' + actions.join('') + '</div></td>' +
                    '</tr>';
            }).join('');

            tableBody.innerHTML = rowsHtml;

            var rows = tableBody.querySelectorAll('tr[data-user-status]');
            rows.forEach(function (row) {
                updateActionButtons(row, row.getAttribute('data-user-status'));
            });
        }

        function performSearch(term) {
            var normalized = term.trim();
            var roleValue = roleSelect.value || 'all';

            setLoading(true);

            var formData = new FormData();
            formData.append('action', 'nymia_admin_search_users');
            formData.append('nonce', window.nymiaDashboardConfig.userSearchNonce || '');
            formData.append('term', normalized);
            if (roleValue && roleValue !== 'all') {
                formData.append('role', roleValue);
            }

            fetch(window.nymiaDashboardConfig.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
                .then(function (response) { return response.json(); })
                .then(function (payload) {
                    if (payload && payload.success) {
                        lastTerm = normalized;
                        lastRole = roleValue;
                        renderUsers((payload.data && payload.data.users) ? payload.data.users : []);
                    } else {
                        lastTerm = null;
                        lastRole = roleValue;
                        window.alert((payload && payload.data && payload.data.message) || searchStrings.error || 'Search failed.');
                    }
                })
                .catch(function () {
                    lastTerm = null;
                    lastRole = roleValue;
                    window.alert(searchStrings.error || 'Search failed.');
                })
                .finally(function () {
                    setLoading(false);
                });
        }

        function handleInput() {
            updateClearButton();
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }
            debounceTimer = setTimeout(function () {
                var term = input.value;
                var currentRole = roleSelect.value || 'all';
                if (term.trim() === (lastTerm || '') && currentRole === (lastRole || 'all')) {
                    return;
                }
                performSearch(term);
            }, 250);
        }

        updateClearButton();

        input.addEventListener('input', handleInput);
        roleSelect.addEventListener('change', function () {
            performSearch(input.value);
        });
    }

    function renderProductsTable(productsData) {
        var body = document.querySelector('.nymia-products-table-body');
        if (!body) {
            return;
        }

        var strings = (window.nymiaDashboardConfig && window.nymiaDashboardConfig.productStrings) || {};

        if (!productsData || !Array.isArray(productsData.table) || !productsData.table.length) {
            var emptyText = strings.emptyTable || 'No products have been uploaded yet.';
            body.innerHTML = '<tr class="nymia-users-empty"><td colspan="6">' + escapeHtml(emptyText) + '</td></tr>';
            return;
        }

        var deleteLabel = strings.deleteLabel || 'Delete';

        var rowsHtml = productsData.table.map(function (product) {
            var typeAttr = escapeAttr(product.type || '');
            var idAttr = escapeAttr(String(product.id || ''));
            var userAttr = escapeAttr(String(product.user_id || ''));
            var titleAttr = escapeAttr(product.title || '');
            var creatorAttr = escapeAttr(product.creator || '');
            var title = escapeHtml(product.title || '');
            var creator = escapeHtml(product.creator || '');
            var status = escapeHtml(product.status || '');
            var statusClass = product.status_slug ? ' is-' + escapeAttr(product.status_slug) : '';
            var submitted = escapeHtml(product.submitted || '');
            var price = escapeHtml(product.price || '');

            return '' +
                '<tr data-product-type="' + typeAttr + '" data-product-id="' + idAttr + '" data-product-user="' + userAttr + '" data-product-title="' + titleAttr + '">' +
                '<td><strong>' + title + '</strong></td>' +
                '<td>' + creator + '</td>' +
                '<td><span class="nymia-status-pill' + statusClass + '">' + status + '</span></td>' +
                '<td>' + submitted + '</td>' +
                '<td>' + price + '</td>' +
                '<td>' +
                    '<button type="button" class="nymia-chip-btn is-danger" data-product-action="delete" data-product-title="' + titleAttr + '" data-product-creator="' + creatorAttr + '">' +
                        escapeHtml(deleteLabel) +
                    '</button>' +
                '</td>' +
                '</tr>';
        }).join('');

        body.innerHTML = rowsHtml;
    }

    function updateProductsView(productsData, eventData) {
        if (!productsData) {
            return;
        }

        renderProductsTable(productsData);

        var strings = getProductStrings();

        var totalEl = document.querySelector('[data-product-count-total]');
        if (totalEl) {
            var totalTemplate = strings.totalLabel || '%d total products';
            totalEl.textContent = totalTemplate.replace('%d', productsData.total_count || 0);
        }

        var sectionActions = document.querySelector('#nymia-tab-products .nymia-section-actions');
        if (sectionActions) {
            var paidTemplate = strings.paidLabel || '%d monetized items';
            var paidEl = sectionActions.querySelector('[data-product-count-paid]');
            if (productsData.paid_count && productsData.paid_count > 0) {
                var paidText = paidTemplate.replace('%d', productsData.paid_count);
                if (!paidEl) {
                    paidEl = document.createElement('span');
                    paidEl.className = 'nymia-pill nymia-pill-warning';
                    paidEl.setAttribute('data-product-count-paid', '');
                    var referenceBtn = sectionActions.querySelector('.nymia-secondary-btn');
                    if (referenceBtn) {
                        sectionActions.insertBefore(paidEl, referenceBtn);
                    } else {
                        sectionActions.appendChild(paidEl);
                    }
                }
                paidEl.textContent = paidText;
            } else if (paidEl && paidEl.parentNode) {
                paidEl.parentNode.removeChild(paidEl);
            }
        }

        handleProductUndo(eventData, strings);

        if (eventData && eventData.deletedData) {
            updateDeletedView(eventData.deletedData);
        } else {
            updateDeletedView();
        }

        initProductActions();
    }

    function initDeletedModule() {
        deletedSearchInput = document.getElementById('nymia-deleted-search-input');
        deletedSearchField = document.getElementById('nymia-deleted-search-field');
        deletedFilterSelect = document.getElementById('nymia-deleted-filter');

        if (!deletedDataCache) {
            deletedDataCache = (hasDashboardData && window.nymiaDashboardData.deleted)
                ? window.nymiaDashboardData.deleted
                : { total_count: 0, audio_count: 0, ebook_count: 0, items: [] };
        }

        if (deletedSearchInput && deletedSearchInput.getAttribute('data-enhanced') !== '1') {
            deletedSearchInput.setAttribute('data-enhanced', '1');
            deletedSearchInput.addEventListener('input', handleDeletedSearchInput);
        }

        if (deletedFilterSelect && deletedFilterSelect.getAttribute('data-enhanced') !== '1') {
            deletedFilterSelect.setAttribute('data-enhanced', '1');
            deletedFilterSelect.addEventListener('change', function () {
                updateDeletedView();
            });
        }

        updateDeletedSearchState();
        updateDeletedView(deletedDataCache);
    }

    function updateDeletedView(newData) {
        if (newData) {
            deletedDataCache = newData;
            if (typeof window.nymiaDashboardData !== 'undefined') {
                window.nymiaDashboardData.deleted = newData;
            }
        } else if (!deletedDataCache) {
            deletedDataCache = { total_count: 0, audio_count: 0, ebook_count: 0, items: [] };
        }

        if (!deletedSearchInput) {
            deletedSearchInput = document.getElementById('nymia-deleted-search-input');
            deletedSearchField = document.getElementById('nymia-deleted-search-field');
        }
        if (!deletedFilterSelect) {
            deletedFilterSelect = document.getElementById('nymia-deleted-filter');
        }

        var filteredItems = filterDeletedItems(deletedDataCache.items || []);
        renderDeletedTable(filteredItems);
        updateDeletedCounts();
        initDeletedActions();
        updateDeletedSearchState();
    }

    function updateDeletedCounts() {
        var strings = getProductStrings();
        var totalChip = document.querySelector('[data-deleted-count-total]');
        if (totalChip) {
            var totalTemplate = strings.deletedTotalLabel || '%d deleted items';
            totalChip.textContent = totalTemplate.replace('%d', deletedDataCache.total_count || 0);
        }

        var audioChip = document.querySelector('[data-deleted-count-audio]');
        if (audioChip) {
            var audioCount = deletedDataCache.audio_count || 0;
            var audioTemplate = strings.deletedAudioLabel || '%d audio';
            audioChip.textContent = audioTemplate.replace('%d', audioCount);
            audioChip.classList.toggle('is-hidden', audioCount === 0);
        }

        var ebookChip = document.querySelector('[data-deleted-count-ebook]');
        if (ebookChip) {
            var ebookCount = deletedDataCache.ebook_count || 0;
            var ebookTemplate = strings.deletedEbookLabel || '%d ebooks';
            ebookChip.textContent = ebookTemplate.replace('%d', ebookCount);
            ebookChip.classList.toggle('is-hidden', ebookCount === 0);
        }
    }

    function filterDeletedItems(items) {
        if (!Array.isArray(items)) {
            return [];
        }

        var term = deletedSearchInput ? deletedSearchInput.value.trim().toLowerCase() : '';
        var typeFilter = deletedFilterSelect ? deletedFilterSelect.value : 'all';

        return items.filter(function (item) {
            var matchesType = typeFilter === 'all' || (item.type || '') === typeFilter;
            if (!matchesType) {
                return false;
            }

            if (!term) {
                return true;
            }

            var haystack = (
                (item.title || '') + ' ' +
                (item.creator || '') + ' ' +
                (item.note || '')
            ).toLowerCase();

            return haystack.indexOf(term) !== -1;
        });
    }

    function renderDeletedTable(items) {
        var body = document.querySelector('.nymia-deleted-table-body');
        if (!body) {
            return;
        }

        var strings = getProductStrings();

        if (!items.length) {
            var emptyText;
            if (!deletedDataCache.items || !deletedDataCache.items.length) {
                emptyText = strings.deletedEmptyAll || 'No deleted content yet.';
            } else {
                emptyText = strings.deletedEmptyFiltered || 'No deleted content matches your filters.';
            }
            body.innerHTML = '<tr class="nymia-users-empty"><td colspan="6">' + escapeHtml(emptyText) + '</td></tr>';
            return;
        }

        var restoreLabel = strings.restoreLabel || 'Restore';

        var rowsHtml = items.map(function (item) {
            var noteCell;
            if (item.note) {
                noteCell = '<span class="nymia-deleted-note" title="' + escapeAttr(item.note) + '">' + escapeHtml(item.note_preview || item.note) + '</span>';
            } else {
                noteCell = '<span class="nymia-deleted-note is-empty">&mdash;</span>';
            }

            return '' +
                '<tr data-product-type="' + escapeAttr(item.type || '') + '" data-product-id="' + escapeAttr(String(item.product_id || '')) + '" data-product-user="' + escapeAttr(String(item.user_id || '')) + '" data-product-title="' + escapeAttr(item.title || '') + '">' +
                    '<td><strong>' + escapeHtml(item.title || '') + '</strong></td>' +
                    '<td>' + escapeHtml(item.type_label || item.type || '') + '</td>' +
                    '<td>' + escapeHtml(item.creator || '') + '</td>' +
                    '<td><span class="nymia-deleted-date" title="' + escapeAttr(item.deleted_exact || '') + '">' + escapeHtml(item.deleted_human || '') + '</span></td>' +
                    '<td>' + noteCell + '</td>' +
                    '<td><button type="button" class="nymia-chip-btn is-success" data-product-restore-token="' + escapeAttr(item.token || '') + '">' + escapeHtml(restoreLabel) + '</button></td>' +
                '</tr>';
        }).join('');

        body.innerHTML = rowsHtml;
    }

    function initDeletedActions() {
        var tableBody = document.querySelector('.nymia-deleted-table-body');
        if (!tableBody) {
            return;
        }

        var strings = getProductStrings();

        tableBody.querySelectorAll('[data-product-restore-token]').forEach(function (button) {
            if (button.dataset.bound === '1') {
                return;
            }
            button.dataset.bound = '1';

            button.addEventListener('click', function () {
                var token = button.getAttribute('data-product-restore-token');
                if (!token) {
                    return;
                }
                restoreProduct(token, button, strings);
            });
        });
    }

    function handleDeletedSearchInput() {
        updateDeletedSearchState();
        if (deletedSearchDebounce) {
            clearTimeout(deletedSearchDebounce);
        }
        deletedSearchDebounce = setTimeout(function () {
            updateDeletedView();
        }, 200);
    }

    function updateDeletedSearchState() {
        if (!deletedSearchInput || !deletedSearchField) {
            return;
        }
        if (deletedSearchInput.value.trim()) {
            deletedSearchField.classList.add('has-value');
        } else {
            deletedSearchField.classList.remove('has-value');
        }
    }

    function handleProductUndo(eventData, strings) {
        var container = document.querySelector('.nymia-product-undo');
        if (!container) {
            return;
        }

        var deletedPayload = eventData && eventData.deleted;
        var restoredPayload = eventData && eventData.restored;

        if (deletedPayload && deletedPayload.token) {
            var deletedName = deletedPayload.title ? deletedPayload.title : (strings.deletedFallbackName || 'This product');
            var messageTemplate = strings.deletedMessage || '“%s” was removed.';
            var messageText = messageTemplate.replace('%s', deletedName);
            var helperText = strings.restoreMessage || '';

            var helperHtml = helperText
                ? '<p class="nymia-product-undo-helper">' + escapeHtml(helperText) + '</p>'
                : '';

            container.innerHTML =
                '<div class="nymia-product-undo-card">' +
                    '<div>' +
                        '<div class="nymia-product-undo-message">' + escapeHtml(messageText) + '</div>' +
                        helperHtml +
                    '</div>' +
                    '<div class="nymia-product-undo-actions">' +
                        '<button type="button" class="nymia-product-restore-btn" data-product-restore-token="' + escapeAttr(deletedPayload.token) + '">' +
                            escapeHtml(strings.restoreLabel || 'Restore') +
                        '</button>' +
                    '</div>' +
                '</div>';

            container.classList.add('is-visible');
            bindProductUndo(container, strings);
        } else if (restoredPayload) {
            var successTemplate = strings.restoreSuccess || 'Product restored successfully.';
            var restoredName = restoredPayload.title ? restoredPayload.title : '';
            if (restoredName && successTemplate.indexOf('%s') !== -1) {
                successTemplate = successTemplate.replace('%s', restoredName);
            } else if (restoredName && successTemplate.indexOf(restoredName) === -1) {
                successTemplate += ' ' + restoredName;
            }

            container.innerHTML =
                '<div class="nymia-product-undo-card">' +
                    '<div class="nymia-product-undo-message">' + escapeHtml(successTemplate) + '</div>' +
                '</div>';
            container.classList.add('is-visible');

            setTimeout(function () {
                clearProductUndo();
            }, 4000);
        } else {
            clearProductUndo();
        }
    }

    function clearProductUndo() {
        var container = document.querySelector('.nymia-product-undo');
        if (!container) {
            return;
        }
        container.classList.remove('is-visible');
        container.innerHTML = '';
    }

    function bindProductUndo(container, strings) {
        if (!container) {
            return;
        }

        var button = container.querySelector('[data-product-restore-token]');
        if (!button) {
            return;
        }

        if (button.dataset.bound === '1') {
            return;
        }
        button.dataset.bound = '1';

        button.addEventListener('click', function () {
            var token = button.getAttribute('data-product-restore-token');
            if (!token) {
                return;
            }
            restoreProduct(token, button, strings);
        });
    }

    function restoreProduct(token, button, strings) {
        var config = window.nymiaDashboardConfig || {};
        var nonce = config.productRestoreNonce || '';

        if (!nonce) {
            window.alert(strings.error || 'Unable to restore the product.');
            return;
        }

        button.disabled = true;

        var formData = new FormData();
        formData.append('action', 'nymia_admin_restore_product');
        formData.append('nonce', nonce);
        formData.append('token', token);

        fetch(config.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
            .then(function (response) { return response.json(); })
            .then(function (payload) {
                if (payload && payload.success && payload.data && payload.data.products) {
                    updateProductsView(payload.data.products, {
                        restored: payload.data.restored_product || null,
                        deletedData: payload.data.deleted || null
                    });
                } else {
                    button.disabled = false;
                    window.alert((payload && payload.data && payload.data.message) || strings.error || 'Unable to restore the product.');
                }
            })
            .catch(function () {
                button.disabled = false;
                window.alert(strings.error || 'Unable to restore the product.');
            });
    }

    function initProductActions() {
        if (typeof window.nymiaDashboardConfig === 'undefined') {
            return;
        }

        var tableBody = document.querySelector('.nymia-products-table-body');
        if (!tableBody) {
            return;
        }

        var config = window.nymiaDashboardConfig;
        var strings = config.productStrings || {};
        var nonce = config.productDeleteNonce || '';

        tableBody.querySelectorAll('[data-product-action="delete"]').forEach(function (button) {
            if (button.dataset.bound === '1') {
                return;
            }
            button.dataset.bound = '1';

            button.addEventListener('click', function () {
                var row = button.closest('tr');
                if (!row) {
                    return;
                }

                var productType = row.getAttribute('data-product-type') || '';
                var productId = row.getAttribute('data-product-id') || '';
                var productUser = row.getAttribute('data-product-user') || '';
                var productTitle = row.getAttribute('data-product-title') || button.getAttribute('data-product-title') || '';

                if (!productType || !productId) {
                    window.alert(strings.error || 'Unable to delete the product.');
                    return;
                }

                showDialog({
                    title: strings.dialogTitle || 'Delete product',
                    message: strings.dialogMessage || 'This action will permanently remove the selected product.',
                    userLabel: productTitle ? productTitle : '',
                    warning: strings.dialogWarning || 'This cannot be undone.',
                    confirmLabel: strings.confirmLabel || 'Delete',
                    confirmStyle: 'danger',
                    noteEnabled: true,
                    noteLabel: strings.noteLabel || 'Notify the creator',
                    notePlaceholder: strings.notePlaceholder || 'Share why this product is being removed.',
                    noteHint: strings.noteHint || '',
                    noteRequired: false,
                    durationEnabled: false,
                    onConfirm: function (noteValue) {
                        button.disabled = true;

                        var formData = new FormData();
                        formData.append('action', 'nymia_admin_delete_product');
                        formData.append('nonce', nonce);
                        formData.append('productType', productType);
                        formData.append('productId', productId);
                        formData.append('productUser', productUser);
                        formData.append('productTitle', productTitle || '');
                        formData.append('productNote', noteValue || '');

                        fetch(window.nymiaDashboardConfig.ajaxUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            body: formData
                        })
                            .then(function (response) { return response.json(); })
                            .then(function (payload) {
                                if (payload && payload.success && payload.data && payload.data.products) {
                                    updateProductsView(payload.data.products, {
                                        deleted: payload.data.deleted_product || null,
                                        deletedData: payload.data.deleted || null
                                    });
                                } else {
                                    window.alert((payload && payload.data && payload.data.message) || strings.error || 'Unable to delete the product.');
                                }
                            })
                            .catch(function () {
                                window.alert(strings.error || 'Unable to delete the product.');
                            })
                            .finally(function () {
                                button.disabled = false;
                            });
                    }
                });
            });
        });
    }

    function initAll() {
        initSalesChart();
        initUsersChart();
        attachRefreshHandler();
        initModuleTabs();
        initKycActions();
        initUserActions();
        initUserSearch();
        clearProductUndo();
        hideTabLoader();
        initProductActions();
        initDeletedModule();
    }

    document.addEventListener('DOMContentLoaded', initAll);
    document.addEventListener('nymiaAdminPageLoaded', initAll);
})();

