<div class="jadwal-page" @if(!$show_form_modal && !$show_detail_modal && !$show_delete_modal && !$show_export_modal && !$show_kegiatan_tugas_modal && !$show_gcal_picker) wire:poll.8s @endif>
    <style>
        /* ================================================================
           JADWAL KEGIATAN — gaya halaman mengikuti desain target
           ================================================================ */
        .jadwal-page {
            --jadwal-card-radius: 12px;
            width: 100%;
            min-width: 0;
        }

        .jadwal-page .jadwal-page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 18px;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border);
        }

        .jadwal-page .jadwal-heading {
            min-width: 0;
        }

        .jadwal-page .page-title {
            margin: 0 0 3px;
            font-size: 1.5rem;
            line-height: 1.25;
            letter-spacing: -0.035em;
        }

        .jadwal-page .jadwal-subtitle {
            margin: 0;
            color: var(--muted);
            font-size: 0.75rem;
        }

        .jadwal-page .jadwal-header-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .jadwal-page .jadwal-header-actions .btn {
            min-height: 38px;
            padding-inline: 15px;
            font-size: 0.8125rem;
            font-weight: 600;
            border-radius: 7px;
        }

        .jadwal-page .tab-list {
            gap: 12px;
            width: fit-content;
            max-width: 100%;
            margin: 12px 0 -15px;
            border-bottom: 0;
        }

        .jadwal-page .tab-item {
            min-width: 84px;
            justify-content: center;
            padding: 9px 12px 11px;
            font-size: 0.8125rem;
            gap: 7px;
        }

        .jadwal-page .tab-item.active {
            border-bottom-width: 2px;
        }

        .jadwal-page .kalender-grid-wrapper {
            display: grid;
            grid-template-columns: minmax(0, 1.42fr) minmax(280px, 1fr);
            align-items: start;
            gap: 18px;
        }

        .jadwal-page .calendar-panel {
            min-width: 0;
            padding: 17px 18px 15px;
            border-radius: var(--jadwal-card-radius);
        }

        .jadwal-page .calendar-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding-bottom: 12px;
            margin-bottom: 11px;
            border-bottom: 1px solid var(--border);
        }

        .jadwal-page .calendar-month-group,
        .jadwal-page .calendar-controls {
            display: flex;
            align-items: center;
        }

        .jadwal-page .calendar-month-group {
            gap: 9px;
            min-width: 0;
        }

        .jadwal-page .calendar-month-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .jadwal-page .calendar-today-button {
            height: 29px;
            padding: 0 10px;
            font-size: 0.6875rem;
            white-space: nowrap;
        }

        .jadwal-page .calendar-controls {
            flex-shrink: 0;
            gap: 5px;
        }

        .jadwal-page .calendar-nav-button {
            width: 29px;
            height: 29px;
            min-width: 29px;
            padding: 0;
            border-radius: 7px;
            font-size: 0.875rem;
        }

        .jadwal-page .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 5px 6px;
            text-align: center;
        }

        .jadwal-page .calendar-weekday {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 25px;
            color: var(--muted);
            font-size: 0.625rem;
            font-weight: 700;
            letter-spacing: 0.035em;
            text-transform: uppercase;
        }

        .jadwal-page .calendar-day {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            width: 100%;
            min-width: 0;
            aspect-ratio: 1 / 1;
            padding: 3px;
            border: 1px solid transparent;
            border-radius: 7px;
            background: transparent;
            color: var(--text);
            font: inherit;
            font-size: 0.6875rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 180ms var(--ease), color 180ms var(--ease), border-color 180ms var(--ease), box-shadow 180ms var(--ease);
        }

        .jadwal-page .calendar-day:hover:not(.is-selected) {
            background: var(--green-50);
        }

        .jadwal-page .calendar-day:focus-visible {
            outline: 2px solid var(--green-600);
            outline-offset: 2px;
        }

        .jadwal-page .calendar-day.is-outside {
            color: #B8BDAF;
        }

        .jadwal-page .calendar-day.is-today:not(.is-selected) {
            border-color: var(--green-700);
            color: var(--green-900);
            font-weight: 700;
        }

        .jadwal-page .calendar-day.is-selected {
            background: var(--green-700);
            color: #FFFFFF;
            font-weight: 700;
            box-shadow: 0 2px 5px rgba(47, 58, 26, 0.13);
        }

        .jadwal-page .calendar-day-dot,
        .jadwal-page .calendar-day-dot-placeholder {
            display: block;
            width: 5px;
            height: 5px;
            flex: 0 0 5px;
            border-radius: 50%;
        }

        .jadwal-page .calendar-day-dot.is-green {
            background: var(--success);
        }

        .jadwal-page .calendar-day-dot.is-red {
            background: var(--danger);
        }

        .jadwal-page .calendar-day.is-selected .calendar-day-dot {
            background: #FFFFFF;
        }

        .jadwal-page .calendar-legend {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            margin-top: 13px;
            padding-top: 11px;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: 0.625rem;
        }

        .jadwal-page .calendar-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .jadwal-page .calendar-legend-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .jadwal-page .calendar-legend-dot.is-green {
            background: var(--success);
        }

        .jadwal-page .calendar-legend-dot.is-red {
            background: var(--danger);
        }

        .jadwal-page .calendar-legend-today {
            width: 8px;
            height: 8px;
            border: 1px solid var(--green-700);
            border-radius: 2px;
        }

        .jadwal-page .agenda-column {
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-width: 0;
        }

        .jadwal-page .agenda-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--surface);
            box-shadow: var(--shadow-sm);
        }

        .jadwal-page .agenda-summary-label {
            display: block;
            margin-bottom: 2px;
            color: var(--muted);
            font-size: 0.5625rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .jadwal-page .agenda-date-title {
            margin: 0;
            font-size: 0.875rem;
            font-weight: 700;
            line-height: 1.35;
        }

        .jadwal-page .agenda-count {
            flex-shrink: 0;
            max-width: 104px;
            padding: 5px 9px;
            border-radius: 999px;
            background: var(--green-100);
            color: var(--green-900);
            font-size: 0.625rem;
            font-weight: 600;
            line-height: 1.3;
            text-align: center;
        }

        .jadwal-page .agenda-event-card {
            min-width: 0;
            padding: 18px 20px;
            border-radius: var(--radius);
            cursor: pointer;
        }

        .jadwal-page .agenda-event-card.is-baru {
            border-left-color: var(--danger);
            box-shadow: inset 3px 0 0 var(--danger), var(--shadow-sm);
        }

        .jadwal-page .agenda-event-card.is-baru:hover {
            box-shadow: inset 3px 0 0 var(--danger), var(--shadow-md);
        }

        .jadwal-page .agenda-event-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }

        .jadwal-page .agenda-event-title {
            min-width: 0;
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.35;
            color: var(--text);
            overflow-wrap: anywhere;
        }

        .jadwal-page .agenda-event-card .badge-baru {
            flex-shrink: 0;
            padding: 3px 11px;
            border-radius: 999px;
            background: var(--danger);
            color: #FFFFFF;
            font-size: 0.6875rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .jadwal-page .agenda-event-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px 18px;
            color: var(--muted);
            font-size: 0.8125rem;
            line-height: 1.45;
        }

        .jadwal-page .agenda-event-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-width: 0;
        }

        .jadwal-page .agenda-event-meta-item svg {
            flex: 0 0 16px;
        }

        .jadwal-page .agenda-event-place {
            overflow-wrap: anywhere;
        }

        .jadwal-page .agenda-empty {
            padding: 28px 16px;
            color: var(--muted);
            text-align: center;
            font-size: 0.8125rem;
        }

        @media (min-width: 1440px) {
            .jadwal-page .kalender-grid-wrapper {
                grid-template-columns: minmax(0, 1.4fr) minmax(320px, 1fr);
                gap: 20px;
            }

            .jadwal-page .calendar-panel {
                padding: 19px 20px 16px;
            }

            .jadwal-page .calendar-grid {
                gap: 7px;
            }

            .jadwal-page .calendar-day {
                font-size: 0.75rem;
            }

            .jadwal-page .agenda-event-card {
                padding: 14px 15px;
            }
        }

        @media (max-width: 1023px) {
            .jadwal-page .kalender-grid-wrapper {
                grid-template-columns: minmax(0, 1.15fr) minmax(260px, 1fr);
                gap: 14px;
            }

            .jadwal-page .calendar-panel {
                padding: 14px;
            }

            .jadwal-page .calendar-grid {
                gap: 4px;
            }

            .jadwal-page .calendar-day {
                font-size: 0.6875rem;
            }

            .jadwal-page .agenda-summary {
                padding: 10px 11px;
            }

            .jadwal-page .agenda-event-card {
                padding: 11px;
            }
        }

        @media (max-width: 767px) {
            .jadwal-page .jadwal-page-header {
                align-items: flex-start;
                margin-bottom: 15px;
            }

            .jadwal-page .kalender-grid-wrapper {
                grid-template-columns: minmax(0, 1fr);
            }

            .jadwal-page .calendar-panel {
                padding: 16px;
            }

            .jadwal-page .calendar-grid {
                gap: 6px;
            }

            .jadwal-page .calendar-day {
                font-size: 0.8125rem;
            }

            .jadwal-page .calendar-weekday {
                font-size: 0.6875rem;
            }

            .jadwal-page .agenda-column {
                gap: 9px;
            }

            .jadwal-page .agenda-event-card {
                padding: 13px 14px;
            }
        }

        @media (max-width: 479px) {
            .jadwal-page .page-title {
                font-size: 1.25rem;
            }

            .jadwal-page .jadwal-page-header {
                gap: 12px;
            }

            .jadwal-page .jadwal-header-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .jadwal-page .jadwal-header-actions .btn {
                width: 100%;
            }

            .jadwal-page .tab-list {
                gap: 3px;
            }

            .jadwal-page .tab-item {
                min-width: 0;
                padding-inline: 12px;
            }

            .jadwal-page .calendar-toolbar {
                gap: 6px;
            }

            .jadwal-page .calendar-month-group {
                gap: 6px;
            }

            .jadwal-page .calendar-month-title {
                font-size: 0.875rem;
            }

            .jadwal-page .calendar-today-button {
                padding-inline: 7px;
                font-size: 0.625rem;
            }

            .jadwal-page .calendar-nav-button {
                width: 27px;
                height: 27px;
                min-width: 27px;
            }

            .jadwal-page .calendar-grid {
                gap: 4px;
            }

            .jadwal-page .calendar-day {
                border-radius: 6px;
                font-size: 0.75rem;
            }

            .jadwal-page .calendar-legend {
                gap: 8px;
            }

            .jadwal-page .agenda-summary {
                align-items: flex-start;
            }
        }

        /* ===== POPUP RINCIAN KEGIATAN ===== */
        .jadwal-page .detail-modal {
            max-width: 600px;
            width: 100%;
            padding: 0;
            border-radius: 16px;
            overflow: hidden;
        }

        .jadwal-page .detail-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 22px 24px 16px;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
        }

        .jadwal-page .detail-heading {
            min-width: 0;
        }

        .jadwal-page .detail-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1.3;
            letter-spacing: -0.02em;
            color: var(--text);
            overflow-wrap: anywhere;
        }

        .jadwal-page .detail-subtitle {
            margin: 4px 0 0;
            font-size: 0.8125rem;
            color: var(--muted);
        }

        .jadwal-page .detail-close {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            padding: 0;
            border: 1px solid var(--border);
            border-radius: 50%;
            background: var(--surface);
            color: var(--text);
            cursor: pointer;
            transition: background-color var(--t-fast) var(--ease), border-color var(--t-fast) var(--ease);
        }

        .jadwal-page .detail-close:hover {
            background: var(--green-50);
            border-color: var(--green-200);
        }

        .jadwal-page .detail-body {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 18px 24px 22px;

        }

        .jadwal-page .detail-info {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px 20px;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--green-50);
        }

        .jadwal-page .detail-info-item {
            display: flex;
            align-items: center;
            gap: 9px;
            min-width: 0;
            font-size: 0.875rem;
            color: var(--text);
        }

        .jadwal-page .detail-info-item svg {
            flex: 0 0 16px;
            color: var(--muted);
        }

        .jadwal-page .detail-info-item span {
            overflow-wrap: anywhere;
        }

        .jadwal-page .detail-info-place {
            grid-column: 1 / -1;
        }

        .jadwal-page .detail-section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 10px;
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--text);
        }

        .jadwal-page .detail-section-title svg {
            color: var(--muted);
        }

        .jadwal-page .detail-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .jadwal-page .detail-chip {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border: 1px solid var(--green-200);
            border-radius: 999px;
            background: var(--green-100);
            color: var(--green-900);
            font-size: 0.75rem;
            font-weight: 500;
            line-height: 1.4;
        }

        .jadwal-page .detail-desc {
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
            color: var(--text);
            font-size: 0.875rem;
            line-height: 1.7;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .jadwal-page .detail-empty {
            color: var(--muted);
            font-size: 0.8125rem;
            font-style: italic;
        }

        .jadwal-page .detail-tugas-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
        }

        .jadwal-page .detail-tugas-head .detail-section-title {
            margin: 0;
        }

        .jadwal-page .detail-tugas-add {
            height: 30px;
            padding: 0 10px;
            font-size: 0.75rem;
            gap: 5px;
        }

        .jadwal-page .detail-tugas-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .jadwal-page .detail-tugas-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface);
        }

        .jadwal-page .detail-tugas-main {
            min-width: 0;
        }

        .jadwal-page .detail-tugas-title {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text);
            overflow-wrap: anywhere;
        }

        .jadwal-page .detail-tugas-meta {
            margin-top: 2px;
            font-size: 0.6875rem;
            color: var(--muted);
        }

        .jadwal-page .detail-tugas-badges {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 6px;
            flex-shrink: 0;
        }

        .jadwal-page .detail-tugas-badges .badge {
            font-size: 0.6875rem;
        }

        .jadwal-page .detail-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 24px;
            border-top: 1px solid var(--border);
            background: var(--green-50);
        }

        .jadwal-page .detail-footer-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .jadwal-page .detail-footer .btn {
            height: 36px;
            padding: 0 16px;
            font-size: 0.8125rem;
            font-weight: 600;
            gap: 7px;
        }

        .jadwal-page .detail-edit {
            border-color: var(--green-900);
        }

        .jadwal-page .detail-delete {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 36px;
            padding: 0 10px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: var(--danger);
            font: inherit;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color var(--t-fast) var(--ease);
        }

        .jadwal-page .detail-delete:hover {
            background: var(--danger-bg);
        }

        @media (max-width: 479px) {
            .jadwal-page .detail-header {
                padding: 18px 16px 14px;
            }

            .jadwal-page .detail-body {
                padding: 16px;
            }

            .jadwal-page .detail-info {
                grid-template-columns: minmax(0, 1fr);
            }

            .jadwal-page .detail-footer {
                flex-wrap: wrap;
                padding: 12px 16px;
            }

            .jadwal-page .detail-tugas-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .jadwal-page .detail-tugas-badges {
                justify-content: flex-start;
            }
        }

        /* ===== POPUP PILIH EVENT GOOGLE CALENDAR ===== */
        .jadwal-page .gcal-modal {
            max-width: 560px;
            width: 100%;
            padding: 0;
            border-radius: 16px;
        }

        .jadwal-page .gcal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
        }

        .jadwal-page .gcal-header-main {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .jadwal-page .gcal-header-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 38px;
            width: 38px;
            height: 38px;
            border: 1px solid var(--green-200);
            border-radius: 10px;
            background: var(--green-100);
            color: var(--green-700);
        }

        .jadwal-page .gcal-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.3;
            color: var(--text);
        }

        .jadwal-page .gcal-subtitle {
            margin: 2px 0 0;
            font-size: 0.75rem;
            color: var(--muted);
        }

        .jadwal-page .gcal-close {
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            transition: background-color var(--t-fast) var(--ease), color var(--t-fast) var(--ease);
        }

        .jadwal-page .gcal-close:hover {
            background: var(--green-50);
            color: var(--text);
        }

        .jadwal-page .gcal-body {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 16px 22px 18px;
        }

        .jadwal-page .gcal-info {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border: 1px solid var(--green-100);
            border-radius: 8px;
            background: var(--green-50);
            color: var(--green-800);
            font-size: 0.75rem;
        }

        .jadwal-page .gcal-info svg {
            flex: 0 0 15px;
        }

        .jadwal-page .gcal-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .jadwal-page .gcal-option {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface);
            cursor: pointer;
            transition: border-color var(--t-fast) var(--ease), background-color var(--t-fast) var(--ease), box-shadow var(--t-fast) var(--ease);
        }

        .jadwal-page .gcal-option:hover:not(.is-selected):not(.is-disabled) {
            border-color: var(--green-200);
            background: var(--green-50);
        }

        .jadwal-page .gcal-option.is-selected {
            border-color: var(--green-700);
            background: var(--green-100);
            box-shadow: 0 0 0 1px var(--green-700);
        }

        .jadwal-page .gcal-option.is-disabled {
            background: var(--green-50);
            cursor: not-allowed;
        }

        .jadwal-page .gcal-option.is-disabled .gcal-option-content {
            opacity: 0.55;
        }

        .jadwal-page .gcal-radio {
            appearance: none;
            -webkit-appearance: none;
            flex: 0 0 18px;
            width: 18px;
            height: 18px;
            margin: 2px 0 0;
            border: 2px solid #B8BDAF;
            border-radius: 50%;
            background: var(--surface);
            cursor: inherit;
            transition: border-color var(--t-fast) var(--ease), background-color var(--t-fast) var(--ease), box-shadow var(--t-fast) var(--ease);
        }

        .jadwal-page .gcal-radio:checked {
            border-color: var(--green-700);
            background: var(--green-700);
            box-shadow: inset 0 0 0 3px var(--surface);
        }

        .jadwal-page .gcal-radio:focus-visible {
            outline: 2px solid var(--green-600);
            outline-offset: 2px;
        }

        .jadwal-page .gcal-radio:disabled {
            opacity: 0.55;
        }

        .jadwal-page .gcal-option-content {
            display: flex;
            flex-direction: column;
            gap: 5px;
            min-width: 0;
            flex: 1;
        }

        .jadwal-page .gcal-option-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .jadwal-page .gcal-option-title {
            min-width: 0;
            font-size: 0.875rem;
            font-weight: 700;
            line-height: 1.35;
            color: var(--text);
            overflow-wrap: anywhere;
        }

        .jadwal-page .gcal-added-badge {
            flex-shrink: 0;
            padding: 2px 9px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--surface);
            color: var(--muted);
            font-size: 0.625rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .jadwal-page .gcal-option-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 16px;
            color: var(--muted);
            font-size: 0.75rem;
            line-height: 1.45;
        }

        .jadwal-page .gcal-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
        }

        .jadwal-page .gcal-meta-item svg {
            flex: 0 0 13px;
        }

        .jadwal-page .gcal-sep {
            opacity: 0.6;
        }

        .jadwal-page .gcal-meta-place {
            overflow-wrap: anywhere;
        }

        .jadwal-page .gcal-empty {
            padding: 28px 16px;
            color: var(--muted);
            text-align: center;
            font-size: 0.8125rem;
        }

        .jadwal-page .gcal-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 22px;
            border-top: 1px solid var(--border);
        }

        .jadwal-page .gcal-account {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
            color: var(--muted);
            font-size: 0.6875rem;
            overflow-wrap: anywhere;
        }

        .jadwal-page .gcal-account strong {
            color: var(--text);
            font-weight: 600;
        }

        .jadwal-page .gcal-footer-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: auto;
        }

        .jadwal-page .gcal-footer .btn {
            height: 36px;
            min-width: 64px;
            padding: 0 16px;
            font-size: 0.8125rem;
            font-weight: 600;
        }

        @media (max-width: 479px) {
            .jadwal-page .gcal-header {
                padding: 16px;
            }

            .jadwal-page .gcal-body {
                padding: 14px 16px;
            }

            .jadwal-page .gcal-footer {
                flex-wrap: wrap;
                padding: 12px 16px;
            }

            .jadwal-page .gcal-option-top {
                align-items: flex-start;
                flex-direction: column;
                gap: 6px;
            }
        }

        /* ===== HEADER HALAMAN, BREADCRUMB & TAB (menimpa aturan lama) ===== */
        .jadwal-page .jadwal-page-header {
            display: block;
            margin-bottom: 20px;
            padding-bottom: 0;
            border-bottom: 0;
        }

        .jadwal-page .jadwal-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            color: var(--muted);
            font-size: 0.75rem;
        }

        .jadwal-page .jadwal-breadcrumb svg {
            flex: 0 0 12px;
            opacity: 0.7;
        }

        .jadwal-page .jadwal-breadcrumb strong {
            color: var(--text);
            font-weight: 600;
        }

        .jadwal-page .page-title {
            margin: 0 0 4px;
            font-size: 1.75rem;
            letter-spacing: -0.03em;
        }

        .jadwal-page .jadwal-subtitle {
            font-size: 0.8125rem;
        }

        .jadwal-page .jadwal-tabbar {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 10px 16px;
            margin-top: 18px;
            border-bottom: 1px solid var(--border);
        }

        .jadwal-page .jadwal-tabbar .tab-list {
            display: flex;
            gap: 6px;
            width: auto;
            max-width: 100%;
            margin: 0;
            border-bottom: 0;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .jadwal-page .jadwal-tabbar .tab-list::-webkit-scrollbar {
            display: none;
        }

        .jadwal-page .jadwal-tabbar .tab-item {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 0;
            margin: 0 0 -1px;
            padding: 10px 14px 13px;
            border: 0;
            border-bottom: 2px solid transparent;
            border-radius: 0;
            background: transparent;
            color: var(--muted);
            font: inherit;
            font-size: 0.8125rem;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
            transition: color var(--t-fast) var(--ease), border-color var(--t-fast) var(--ease);
        }

        .jadwal-page .jadwal-tabbar .tab-item::before,
        .jadwal-page .jadwal-tabbar .tab-item::after {
            content: none;
        }

        .jadwal-page .jadwal-tabbar .tab-item svg {
            flex: 0 0 16px;
        }

        .jadwal-page .jadwal-tabbar .tab-item:hover:not(.active) {
            color: var(--text);
        }

        .jadwal-page .jadwal-tabbar .tab-item.active {
            color: var(--text);
            border-bottom-color: var(--green-700);
        }

        .jadwal-page .tab-count {
            padding: 1px 8px;
            border-radius: 999px;
            background: var(--green-100);
            color: var(--green-900);
            font-size: 0.6875rem;
            font-weight: 700;
            line-height: 1.5;
        }

        .jadwal-page .jadwal-tabbar .jadwal-header-actions {
            padding-bottom: 10px;
        }

        .jadwal-page .jadwal-tabbar .jadwal-header-actions .btn {
            gap: 8px;
            font-weight: 700;
        }

        @media (max-width: 479px) {
            .jadwal-page .jadwal-tabbar .jadwal-header-actions {
                width: 100%;
            }
        }

        /* ===== TAB KEGIATAN ===== */
        .jadwal-page .kg-monthbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-bottom: 26px;
            padding: 12px 14px;
            border-radius: var(--radius);
        }

        .jadwal-page .kg-month-switch {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface);
            overflow: hidden;
        }

        .jadwal-page .kg-month-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 36px;
            padding: 0;
            border: 0;
            background: transparent;
            color: var(--text);
            cursor: pointer;
            transition: background-color var(--t-fast) var(--ease);
        }

        .jadwal-page .kg-month-arrow:hover {
            background: var(--green-50);
        }

        .jadwal-page .kg-month-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 36px;
            padding: 0 16px;
            border-left: 1px solid var(--border);
            border-right: 1px solid var(--border);
            color: var(--text);
            font-size: 0.875rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .jadwal-page .kg-month-label svg {
            color: var(--muted);
        }

        .jadwal-page .kg-today-btn {
            height: 30px;
            padding: 0 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .jadwal-page .kg-groups {
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        .jadwal-page .kg-day-head {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .jadwal-page .kg-today-badge {
            flex-shrink: 0;
            padding: 3px 9px;
            border-radius: 5px;
            background: var(--green-100);
            color: var(--green-800);
            font-size: 0.625rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .jadwal-page .kg-day-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: var(--text);
            white-space: nowrap;
        }

        .jadwal-page .kg-day-line {
            flex: 1;
            min-width: 20px;
            height: 1px;
            background: var(--border);
        }

        .jadwal-page .kg-day-count {
            flex-shrink: 0;
            color: var(--muted);
            font-size: 0.75rem;
        }

        .jadwal-page .kg-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .jadwal-page .kg-row {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            border-radius: var(--radius);
            cursor: pointer;
        }

        .jadwal-page .kg-row.is-baru {
            box-shadow: inset 3px 0 0 var(--danger), var(--shadow-sm);
        }

        .jadwal-page .kg-row.is-baru:hover {
            box-shadow: inset 3px 0 0 var(--danger), var(--shadow-md);
        }

        .jadwal-page .kg-date-tile {
            flex: 0 0 54px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 54px;
            padding: 7px 0;
            border-radius: 10px;
            background: var(--green-100);
            color: var(--green-900);
            line-height: 1.15;
        }

        .jadwal-page .kg-date-month {
            font-size: 0.625rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .jadwal-page .kg-date-day {
            font-size: 1.125rem;
            font-weight: 800;
        }

        .jadwal-page .kg-row-main {
            flex: 1;
            min-width: 0;
        }

        .jadwal-page .kg-row-top {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 6px;
        }

        .jadwal-page .kg-row-title {
            min-width: 0;
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.35;
            color: var(--text);
            overflow-wrap: anywhere;
        }

        .jadwal-page .kg-row .badge-baru {
            flex-shrink: 0;
            padding: 3px 11px;
            border-radius: 999px;
            background: var(--danger);
            color: #FFFFFF;
            font-size: 0.6875rem;
            font-weight: 700;
            line-height: 1.4;
        }

        .jadwal-page .kg-row-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 18px;
            color: var(--muted);
            font-size: 0.8125rem;
            line-height: 1.45;
        }

        .jadwal-page .kg-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .jadwal-page .kg-meta-item svg {
            flex: 0 0 14px;
        }

        .jadwal-page .kg-empty {
            padding: 48px 24px;
            color: var(--muted);
            text-align: center;
            font-size: 0.9375rem;
        }

        @media (max-width: 767px) {
            .jadwal-page .kg-day-title {
                white-space: normal;
                font-size: 1.0625rem;
            }

            .jadwal-page .kg-day-line {
                display: none;
            }

            .jadwal-page .kg-day-head {
                flex-wrap: wrap;
                gap: 6px 10px;
            }

            .jadwal-page .kg-day-count {
                margin-left: auto;
            }
        }

        @media (max-width: 479px) {
            .jadwal-page .kg-row {
                gap: 12px;
                padding: 14px;
            }

            .jadwal-page .kg-date-tile {
                flex-basis: 46px;
                width: 46px;
            }
        }

        /* ===== FILTER & PENCARIAN ===== */
        [x-cloak] {
            display: none !important;
        }

        .jadwal-page .fb-search {
            position: relative;
            display: flex;
            align-items: center;
            flex: 1 1 240px;
            min-width: 0;
        }

        .jadwal-page .fb-search>svg {
            position: absolute;
            left: 12px;
            width: 16px;
            height: 16px;
            color: var(--muted);
            pointer-events: none;
        }

        .jadwal-page .fb-search input {
            width: 100%;
            height: 38px;
            padding: 0 36px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface);
            color: var(--text);
            font: inherit;
            font-size: 0.8125rem;
            transition: border-color var(--t-fast) var(--ease), box-shadow var(--t-fast) var(--ease);
        }

        .jadwal-page .fb-search input::placeholder {
            color: var(--muted);
        }

        .jadwal-page .fb-search input:focus {
            outline: none;
            border-color: var(--green-600);
            box-shadow: 0 0 0 3px var(--green-100);
        }

        .jadwal-page .fb-search input[type="search"]::-webkit-search-cancel-button,
        .jadwal-page .fb-search input[type="search"]::-webkit-search-decoration {
            -webkit-appearance: none;
            appearance: none;
            display: none;
        }

        .jadwal-page .fb-clear {
            position: absolute;
            right: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            padding: 0;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            transition: background-color var(--t-fast) var(--ease), color var(--t-fast) var(--ease);
        }

        .jadwal-page .fb-clear:hover {
            background: var(--green-50);
            color: var(--text);
        }

        /* Pencarian di bilah bulan (tab Kegiatan) */
        .jadwal-page .kg-search {
            flex: 0 1 320px;
            margin-left: auto;
        }

        /* Bilah filter (tab Riwayat) */
        .jadwal-page .fb-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: var(--radius);
        }

        .jadwal-page .fb-select {
            flex: 0 1 180px;
            height: 38px;
            min-width: 150px;
            padding: 0 34px 0 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface) url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236B7360' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>") no-repeat right 12px center;
            color: var(--text);
            font: inherit;
            font-size: 0.8125rem;
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
            transition: border-color var(--t-fast) var(--ease), box-shadow var(--t-fast) var(--ease);
        }

        .jadwal-page .fb-select:focus {
            outline: none;
            border-color: var(--green-600);
            box-shadow: 0 0 0 3px var(--green-100);
        }

        .jadwal-page .fb-reset {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 38px;
            padding: 0 12px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: var(--green-800);
            font: inherit;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color var(--t-fast) var(--ease);
        }

        .jadwal-page .fb-reset:hover {
            background: var(--green-50);
        }

        .jadwal-page .fb-info {
            margin: -8px 0 16px;
            color: var(--muted);
            font-size: 0.75rem;
        }

        .jadwal-page .fb-info strong {
            color: var(--text);
        }

        @media (max-width: 767px) {
            .jadwal-page .kg-search {
                flex: 1 1 100%;
                margin-left: 0;
            }

            .jadwal-page .fb-select {
                flex: 1 1 calc(50% - 5px);
                min-width: 0;
            }
        }

        /* ===== PEMILIH BULAN (RIWAYAT) ===== */
        .jadwal-page .fb-bar {
            overflow: visible;
        }

        .jadwal-page .fb-month {
            position: relative;
            flex: 0 0 auto;
        }

        .jadwal-page .fb-month .kg-month-switch {
            height: 38px;
        }

        .jadwal-page .fb-month .kg-month-arrow {
            height: 36px;
        }

        .jadwal-page .fb-month-label {
            min-width: 160px;
            justify-content: center;
            border-top: 0;
            border-bottom: 0;
            background: transparent;
            font-family: inherit;
            cursor: pointer;
            transition: background-color var(--t-fast) var(--ease);
        }

        .jadwal-page .fb-month-label:hover {
            background: var(--green-50);
        }

        .jadwal-page .fb-pop {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            z-index: 30;
            width: 264px;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--surface);
            box-shadow: var(--shadow-md);
        }

        .jadwal-page .fb-pop-year {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.875rem;
            color: var(--text);
        }

        .jadwal-page .fb-pop-year .kg-month-arrow {
            width: 30px;
            height: 30px;
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        .jadwal-page .fb-pop-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 6px;
        }

        .jadwal-page .fb-pop-month {
            position: relative;
            height: 36px;
            border: 1px solid transparent;
            border-radius: 8px;
            background: transparent;
            color: var(--text);
            font: inherit;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color var(--t-fast) var(--ease), color var(--t-fast) var(--ease);
        }

        .jadwal-page .fb-pop-month:hover {
            background: var(--green-50);
        }

        .jadwal-page .fb-pop-month.has-data::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 50%;
            width: 4px;
            height: 4px;
            margin-left: -2px;
            border-radius: 50%;
            background: var(--success);
        }

        .jadwal-page .fb-pop-month.is-active {
            background: var(--green-700);
            color: #FFFFFF;
        }

        .jadwal-page .fb-pop-month.is-active.has-data::after {
            background: #FFFFFF;
        }

        .jadwal-page .fb-pop-foot {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid var(--border);
        }

        .jadwal-page .fb-pop-link {
            padding: 4px 8px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: var(--green-800);
            font: inherit;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
        }

        .jadwal-page .fb-pop-link:hover {
            background: var(--green-50);
        }

        @media (max-width: 767px) {
            .jadwal-page .fb-month {
                flex: 1 1 100%;
            }

            .jadwal-page .fb-month .kg-month-switch {
                width: 100%;
            }

            .jadwal-page .fb-month-label {
                flex: 1;
            }

            .jadwal-page .fb-pop {
                width: 100%;
            }
        }
    </style>

    {{-- HEADER HALAMAN & TAB NAVIGASI --}}
    @php
    $kegiatanBulanCount = ($active_tab === 'kegiatan' && isset($daftarBulananGrouped))
    ? $daftarBulananGrouped->sum(fn ($items) => count($items))
    : null;
    @endphp
    <div class="jadwal-page-header">


        <h1 class="page-title">Jadwal Kegiatan</h1>
        <p class="jadwal-subtitle">Pemantauan dan koordinasi agenda biro secara berkala</p>

        <div class="jadwal-tabbar">
            {{-- Tab: Kalender | Kegiatan | Riwayat --}}
            <div class="tab-list" role="tablist">
                <button type="button" wire:click="setTab('kalender')" class="tab-item {{ $active_tab === 'kalender' ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="2" />
                        <path d="M3 9h18M3 15h18M9 3v18M15 3v18" />
                    </svg>
                    <span>Kalender</span>
                </button>
                <button type="button" wire:click="setTab('kegiatan')" class="tab-item {{ $active_tab === 'kegiatan' ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    <span>Kegiatan</span>
                    @if($kegiatanBulanCount !== null)
                    <span class="tab-count">{{ $kegiatanBulanCount }}</span>
                    @endif
                </button>
                <button type="button" wire:click="setTab('riwayat')" class="tab-item {{ $active_tab === 'riwayat' ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 12a9 9 0 1 0 3-6.7L3 8" />
                        <path d="M3 3v5h5" />
                        <path d="M12 7v5l4 2" />
                    </svg>
                    <span>Riwayat</span>
                </button>
            </div>

            {{-- Tombol Aksi Kanan --}}
            <div class="jadwal-header-actions">
                @if($active_tab === 'riwayat')
                <button type="button" class="btn btn-primary" wire:click="openExportModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" x2="12" y1="15" y2="3" />
                    </svg>
                    <span>Export PDF</span>
                </button>
                @elseif($isKabag && in_array($active_tab, ['kalender', 'kegiatan'], true))
                <button type="button" class="btn btn-primary" wire:click="openCreateModal">
                    <x-icon name="plus" :size="18" />
                    <span>Tambah Kegiatan</span>
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- TAB 1: KALENDER --}}
    @if($active_tab === 'kalender')
    <div class="kalender-grid-wrapper">
        {{-- KOLOM KIRI: KALENDER BULANAN --}}
        <div class="card calendar-panel">
            <div class="calendar-toolbar">
                <div class="calendar-month-group">
                    <h2 class="calendar-month-title">{{ $monthName }}</h2>
                    <button
                        type="button"
                        wire:click="goToToday"
                        class="btn btn-secondary calendar-today-button">
                        Hari Ini
                    </button>
                </div>
                <div class="calendar-controls">
                    <button type="button" class="btn btn-secondary calendar-nav-button"
                        wire:click="prevMonth"
                        title="Bulan Sebelumnya"
                        aria-label="Bulan sebelumnya">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6" />
                        </svg>
                    </button>

                    <button type="button" class="btn btn-secondary calendar-nav-button"
                        wire:click="nextMonth"
                        title="Bulan Berikutnya"
                        aria-label="Bulan berikutnya">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Grid 7 Kolom Kalender (Senin - Minggu) --}}
            <div class="calendar-grid">
                @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayHeader)
                <div class="calendar-weekday">{{ $dayHeader }}</div>
                @endforeach

                @foreach($kalenderGrid as $cell)
                @php
                $isSelected = $cell['is_selected'];
                $isToday = $cell['is_today'];
                $isCurrentMonth = $cell['is_current_month'];
                $dotColor = $cell['dot']; // 'green'|'red'|null
                @endphp
                <button
                    type="button"
                    wire:click="selectDate('{{ $cell['date'] }}')"
                    class="calendar-day {{ $isSelected ? 'is-selected' : '' }} {{ $isToday ? 'is-today' : '' }} {{ !$isCurrentMonth ? 'is-outside' : '' }}">
                    <span>{{ $cell['day'] }}</span>
                    {{-- Titik indikator kegiatan: hijau bila sudah dilihat, merah bila belum dilihat --}}
                    @if($dotColor)
                    <span class="calendar-day-dot {{ $dotColor === 'red' ? 'is-red' : 'is-green' }}"></span>
                    @else
                    <span class="calendar-day-dot-placeholder"></span>
                    @endif
                </button>
                @endforeach
            </div>

            <div class="calendar-legend" aria-label="Keterangan kalender">
                <span class="calendar-legend-item"><span class="calendar-legend-dot is-green"></span> Kegiatan terjadwal</span>
                <span class="calendar-legend-item"><span class="calendar-legend-dot is-red"></span> Kegiatan baru (belum dilihat)</span>
                <span class="calendar-legend-item"><span class="calendar-legend-today"></span> Hari ini</span>
            </div>
        </div>

        {{-- KOLOM KANAN: DAFTAR KEGIATAN PADA TANGGAL TERPILIH --}}
        <div class="agenda-column">
            <div class="agenda-summary">
                <div>
                    <span class="agenda-summary-label">Agenda Terpilih</span>
                    <h2 class="agenda-date-title">{{ $selectedDateFormatted }}</h2>
                </div>
                <span class="agenda-count">{{ $kalenderKegiatans->count() }} Kegiatan</span>
            </div>

            @forelse($kalenderKegiatans as $kegiatan)
            @php
            $isBaru = isset($unread_card_ids[$kegiatan->id]);
            @endphp
            <div
                class="card card-clickable agenda-event-card {{ $isBaru ? 'is-baru' : '' }}"
                wire:click="openDetailModal({{ $kegiatan->id }})"
                wire:key="kalender-kegiatan-{{ $kegiatan->id }}">
                <div class="agenda-event-heading">
                    <h3 class="agenda-event-title">{{ $kegiatan->judul }}</h3>
                    @if($isBaru)
                    <span class="badge badge-baru">Baru</span>
                    @endif
                </div>

                <div class="agenda-event-meta">
                    <div class="agenda-event-meta-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        <span>{{ $kegiatan->waktu_formatted }}</span>
                    </div>
                    <div class="agenda-event-meta-item agenda-event-place">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        <span>{{ $kegiatan->tempat }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="card agenda-empty">
                <p>Tidak ada jadwal kegiatan pada tanggal ini.</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- TAB 2: KEGIATAN (DAFTAR BULANAN) --}}
    @elseif($active_tab === 'kegiatan')
    <div x-data="{
        q: '',
        n() { return this.q.trim().toLowerCase(); },
        hit(el) { const n = this.n(); return !n || (el.dataset.judul || '').includes(n); },
        grp(sec) { const n = this.n(); return !n || Array.from(sec.querySelectorAll('[data-judul]')).some(c => (c.dataset.judul || '').includes(n)); },
        none(list) { const n = this.n(); if (!n || !list) return false; return !Array.from(list.querySelectorAll('[data-judul]')).some(c => (c.dataset.judul || '').includes(n)); }
    }">
        {{-- Navigasi bulan + pencarian --}}
        <div class="card kg-monthbar">
            <div class="kg-month-switch">
                <button type="button" class="kg-month-arrow" wire:click="prevMonth" aria-label="Bulan sebelumnya">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                </button>
                <span class="kg-month-label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    {{ $monthName }}
                </span>
                <button type="button" class="kg-month-arrow" wire:click="nextMonth" aria-label="Bulan berikutnya">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </button>
            </div>
            <button type="button" wire:click="goToToday" class="btn btn-secondary kg-today-btn">Bulan Ini</button>

            <div class="fb-search kg-search">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input
                    type="search"
                    x-model="q"
                    placeholder="Cari judul kegiatan..."
                    aria-label="Cari judul kegiatan"
                    autocomplete="off">
                <button type="button" class="fb-clear" x-show="q" x-cloak @click="q = ''" aria-label="Hapus pencarian">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>
        </div>

        @if($daftarBulananGrouped->isEmpty())
        <div class="card kg-empty">
            <p>Tidak ada jadwal kegiatan pada bulan {{ $monthName }}.</p>
        </div>
        @else
        <div class="kg-groups" x-ref="kglist">
            @foreach($daftarBulananGrouped as $dateKey => $items)
            @php
            $dateCarbon = \Carbon\Carbon::parse($dateKey, 'Asia/Jakarta');
            @endphp
            <section wire:key="kg-group-{{ $dateKey }}" x-show="grp($el)">
                {{-- Judul kelompok tanggal --}}
                <div class="kg-day-head">
                    @if($dateCarbon->isToday())
                    <span class="kg-today-badge">Hari ini</span>
                    @endif
                    <h3 class="kg-day-title">{{ $dateCarbon->translatedFormat('l, j F Y') }}</h3>
                    <span class="kg-day-line"></span>
                    <span class="kg-day-count">{{ count($items) }} Agenda Terdaftar</span>
                </div>

                <div class="kg-list">
                    @foreach($items as $kegiatan)
                    @php
                    $isBaruRow = !$kegiatan->is_dilihat;
                    $picCount = $kegiatan->pics->count();
                    $picLabel = $picCount === 1 ? $kegiatan->pics->first()->name : $picCount . ' orang';
                    @endphp
                    <div
                        class="card card-clickable kg-row {{ $isBaruRow ? 'is-baru' : '' }}"
                        wire:click="openDetailModal({{ $kegiatan->id }})"
                        wire:key="bulanan-kegiatan-{{ $kegiatan->id }}"
                        data-judul="{{ mb_strtolower($kegiatan->judul) }}"
                        x-show="hit($el)">
                        <div class="kg-date-tile" aria-hidden="true">
                            <span class="kg-date-month">{{ $dateCarbon->translatedFormat('M') }}</span>
                            <span class="kg-date-day">{{ $dateCarbon->format('d') }}</span>
                        </div>

                        <div class="kg-row-main">
                            <div class="kg-row-top">
                                <h4 class="kg-row-title">{{ $kegiatan->judul }}</h4>
                                @if($isBaruRow)
                                <span class="badge badge-baru">Baru</span>
                                @endif
                            </div>

                            <div class="kg-row-meta">
                                <span class="kg-meta-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                    {{ $kegiatan->waktu_formatted }}
                                </span>
                                <span class="kg-meta-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                    {{ $kegiatan->tempat }}
                                </span>
                                @if($picCount > 0)
                                <span class="kg-meta-item" title="{{ $kegiatan->pics->pluck('name')->join(', ') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                    </svg>
                                    PIC: {{ $picLabel }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endforeach
        </div>

        <div class="card kg-empty" x-show="none($refs.kglist)" x-cloak>
            <p>Tidak ada kegiatan dengan judul tersebut pada bulan {{ $monthName }}.</p>
        </div>
        @endif
    </div>

    {{-- TAB 3: RIWAYAT (KEGIATAN SEBELUM HARI INI WIB) --}}
    @elseif($active_tab === 'riwayat')
    @php
    $rwSemua = $riwayatKegiatans;
    $rwBulanOpsi = $rwSemua
    ->map(fn ($k) => \Carbon\Carbon::parse($k->tanggal, 'Asia/Jakarta')->format('Y-m'))
    ->unique()->sortDesc()->values();
    $rwPicOpsi = $rwSemua->flatMap(fn ($k) => $k->pics)->unique('id')->sortBy('name')->values();
    $rwNowYm = \Carbon\Carbon::now('Asia/Jakarta')->format('Y-m');
    $rwAda = $rwBulanOpsi->all();
    @endphp
    <div x-data="{
        q: '', bulan: '', pic: '',
        open: false,
        py: {{ (int) substr($rwNowYm, 0, 4) }},
        nowYm: '{{ $rwNowYm }}',
        ada: @js($rwAda),
        nama: ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
        pendek: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
        n() { return this.q.trim().toLowerCase(); },
        aktif() { return !!(this.q.trim() || this.bulan || this.pic); },
        label() {
            if (!this.bulan) return 'Semua bulan';
            const p = this.bulan.split('-');
            return this.nama[Number(p[1]) - 1] + ' ' + p[0];
        },
        ym(i) { return this.py + '-' + String(i + 1).padStart(2, '0'); },
        geser(d) {
            const p = (this.bulan || this.nowYm).split('-').map(Number);
            let y = p[0], m = p[1] + d;
            if (m < 1) { m = 12; y--; } else if (m > 12) { m = 1; y++; }
            this.bulan = y + '-' + String(m).padStart(2, '0');
            this.py = y;
        },
        pilih(i) { this.bulan = this.ym(i); this.open = false; },
        hit(el) {
            const d = el.dataset, n = this.n();
            return (!n || (d.judul || '').includes(n))
                && (!this.bulan || d.bulan === this.bulan)
                && (!this.pic || (d.pics || '').includes('|' + this.pic + '|'));
        },
        semua(list) { return list ? list.querySelectorAll('[data-judul]').length : 0; },
        tampil(list) { return list ? Array.from(list.querySelectorAll('[data-judul]')).filter(el => this.hit(el)).length : 0; },
        reset() { this.q = ''; this.bulan = ''; this.pic = ''; }
    }">
        <div style="margin-bottom: 16px;">
            <h2 style="font-size: 1.125rem; font-weight: 700; color: var(--text);">Riwayat Kegiatan</h2>
            <p style="font-size: 0.8125rem; color: var(--muted);">Daftar seluruh kegiatan lampau sebelum hari ini (WIB), diurutkan dari yang terbaru.</p>
        </div>

        @if($rwSemua->isEmpty())
        <div class="card" style="text-align: center; padding: 48px 24px; color: var(--muted);">
            <p style="font-size: 1rem;">Belum ada riwayat kegiatan lampau.</p>
        </div>
        @else
        {{-- Bilah filter --}}
        <div class="card fb-bar">
            <div class="fb-search">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input
                    type="search"
                    x-model="q"
                    placeholder="Cari judul kegiatan..."
                    aria-label="Cari judul kegiatan"
                    autocomplete="off">
                <button type="button" class="fb-clear" x-show="q" x-cloak @click="q = ''" aria-label="Hapus pencarian">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>

            <div class="fb-month" @click.outside="open = false" @keydown.escape.window="open = false">
                <div class="kg-month-switch">
                    <button type="button" class="kg-month-arrow" @click="geser(-1)" aria-label="Bulan sebelumnya">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="15 18 9 12 15 6" />
                        </svg>
                    </button>
                    <button type="button" class="kg-month-label fb-month-label"
                        @click="open = !open; if (open) py = bulan ? Number(bulan.split('-')[0]) : py"
                        :aria-expanded="open">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                        <span x-text="label()"></span>
                    </button>
                    <button type="button" class="kg-month-arrow" @click="geser(1)" aria-label="Bulan berikutnya">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="9 18 15 12 9 6" />
                        </svg>
                    </button>
                </div>

                <div class="fb-pop" x-show="open" x-cloak x-transition.opacity.duration.150ms>
                    <div class="fb-pop-year">
                        <button type="button" class="kg-month-arrow" @click="py--" aria-label="Tahun sebelumnya">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="15 18 9 12 15 6" />
                            </svg>
                        </button>
                        <strong x-text="py"></strong>
                        <button type="button" class="kg-month-arrow" @click="py++" aria-label="Tahun berikutnya">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="9 18 15 12 9 6" />
                            </svg>
                        </button>
                    </div>

                    <div class="fb-pop-grid">
                        <template x-for="(nm, i) in pendek" :key="i">
                            <button type="button" class="fb-pop-month"
                                :class="{ 'is-active': bulan === ym(i), 'has-data': ada.includes(ym(i)) }"
                                @click="pilih(i)" x-text="nm"></button>
                        </template>
                    </div>

                    <div class="fb-pop-foot">
                        <button type="button" class="fb-pop-link" @click="bulan = ''; open = false">Semua bulan</button>
                        <button type="button" class="fb-pop-link"
                            @click="bulan = nowYm; py = Number(nowYm.split('-')[0]); open = false">Bulan ini</button>
                    </div>
                </div>
            </div>

            <select class="fb-select" x-model="pic" aria-label="Filter PIC">
                <option value="">Semua PIC</option>
                @foreach($rwPicOpsi as $picOpt)
                <option value="{{ $picOpt->id }}">{{ $picOpt->name }}</option>
                @endforeach
            </select>

            <button type="button" class="fb-reset" x-show="aktif()" x-cloak @click="reset()">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="1 4 1 10 7 10" />
                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10" />
                </svg>
                Reset
            </button>
        </div>



        <div x-ref="rwlist" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 14px;">
            @foreach($rwSemua as $kegiatan)
            <div
                class="card card-clickable"
                wire:click="openDetailModal({{ $kegiatan->id }})"
                wire:key="riwayat-kegiatan-{{ $kegiatan->id }}"
                data-judul="{{ mb_strtolower($kegiatan->judul) }}"
                data-bulan="{{ \Carbon\Carbon::parse($kegiatan->tanggal, 'Asia/Jakarta')->format('Y-m') }}"
                data-pics="|{{ $kegiatan->pics->pluck('id')->implode('|') }}|"
                x-show="hit($el)"
                style="padding: 16px;">
                <div style="margin-bottom: 6px;">
                    <span style="font-size: 0.75rem; font-weight: 600; color: var(--muted);">
                        {{ \Carbon\Carbon::parse($kegiatan->tanggal, 'Asia/Jakarta')->translatedFormat('d F Y') }}
                    </span>
                </div>

                <h4 style="font-size: 1rem; font-weight: 600; color: var(--text); margin-bottom: 8px;">
                    {{ $kegiatan->judul }}
                </h4>

                <div style="font-size: 0.8125rem; color: var(--muted); margin-bottom: 8px; display: flex; flex-direction: column; gap: 4px;">
                    <div><strong>Waktu:</strong> {{ $kegiatan->waktu_formatted }}</div>
                    <div><strong>Tempat:</strong> {{ $kegiatan->tempat }}</div>
                </div>

                @if($kegiatan->pics->isNotEmpty())
                <div style="font-size: 0.75rem; color: var(--green-800);">
                    <strong>PIC:</strong> {{ $kegiatan->pics->pluck('name')->join(', ') }}
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <div class="card" x-show="aktif() && tampil($refs.rwlist) === 0" x-cloak style="text-align: center; padding: 48px 24px; color: var(--muted);">
            <p style="font-size: 1rem;">Tidak ada kegiatan yang cocok dengan filter.</p>
        </div>
        @endif
    </div>
    @endif

    {{-- MODAL POPUP RINCIAN KEGIATAN --}}
    <div
        class="modal-backdrop {{ $show_detail_modal ? 'open' : '' }}"
        x-data
        @keydown.escape.window="$wire.closeDetailModal()"
        @if(!$show_detail_modal) style="display: none;" @endif>
        <div class="modal-box detail-modal">
            @if($detail_kegiatan)
            <div class="modal-header detail-header">
                <div class="detail-heading">
                    <h3 class="modal-title detail-title">{{ $detail_kegiatan->judul }}</h3>
                    <p class="detail-subtitle">Rincian jadwal kegiatan dan tugas.</p>
                </div>
                <button type="button" class="modal-close detail-close" wire:click="closeDetailModal" aria-label="Tutup">
                    <x-icon name="x" :size="18" />
                </button>
            </div>

            <div class="modal-body detail-body">
                {{-- Info ringkas: tanggal, waktu, tempat --}}
                <div class="detail-info">
                    <div class="detail-info-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                        <span>{{ \Carbon\Carbon::parse($detail_kegiatan->tanggal, 'Asia/Jakarta')->translatedFormat('l, j F Y') }}</span>
                    </div>
                    <div class="detail-info-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        <span>{{ $detail_kegiatan->waktu_formatted }}</span>
                    </div>
                    <div class="detail-info-item detail-info-place">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        <span>{{ $detail_kegiatan->tempat }}</span>
                    </div>
                </div>

                {{-- Ditugaskan kepada (PIC) --}}
                <section>
                    <h4 class="detail-section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                        Ditugaskan kepada
                    </h4>
                    @if($detail_kegiatan->pics->isNotEmpty())
                    <div class="detail-chips">
                        @foreach($detail_kegiatan->pics as $pic)
                        @php
                        $picRole = $pic->role instanceof \BackedEnum ? $pic->role->value : (string) $pic->role;
                        @endphp
                        <span class="detail-chip">{{ $pic->name }} &middot; {{ ucfirst($picRole) }}</span>
                        @endforeach
                    </div>
                    @else
                    <span class="detail-empty">Tidak ada PIC yang ditugaskan.</span>
                    @endif
                </section>

                {{-- Deskripsi --}}
                <section>
                    <h4 class="detail-section-title">Deskripsi Tugas</h4>
                    @if(!empty($detail_kegiatan->deskripsi))
                    <div class="detail-desc">{{ $detail_kegiatan->deskripsi }}</div>
                    @else
                    <div class="detail-desc detail-empty">Belum ada deskripsi.</div>
                    @endif
                </section>

                {{-- Daftar Tugas Terkait & Tombol Tambah Tugas (Kabag) --}}
                <section>
                    <div class="detail-tugas-head">
                        <h4 class="detail-section-title">Tugas Terkait</h4>
                        @if($isKabag)
                        <button type="button" class="btn btn-secondary detail-tugas-add" wire:click="openKegiatanTugasModal">
                            <x-icon name="plus" :size="13" />
                            <span>Tambah Tugas</span>
                        </button>
                        @endif
                    </div>

                    @if($detail_kegiatan->tugas && $detail_kegiatan->tugas->isNotEmpty())
                    <div class="detail-tugas-list">
                        @foreach($detail_kegiatan->tugas as $kegTugas)
                        @php
                        $kegTugasStatusClass = match($kegTugas->status->value) {
                        'selesai' => 'badge-status-selesai',
                        'sedang_dikerjakan' => 'badge-status-sedang',
                        default => 'badge-status-belum',
                        };
                        @endphp
                        <div class="detail-tugas-item">
                            <div class="detail-tugas-main">
                                <div class="detail-tugas-title">{{ $kegTugas->judul }}</div>
                                <div class="detail-tugas-meta">
                                    PIC: <strong>{{ $kegTugas->pic?->name ?? '-' }}</strong> &bull; {{ $kegTugas->deadline_formatted }}
                                </div>
                            </div>
                            <div class="detail-tugas-badges">
                                <span class="badge badge-prioritas-{{ $kegTugas->prioritas->value }}">{{ $kegTugas->prioritas->label() }}</span>
                                <span class="badge {{ $kegTugasStatusClass }}">{{ $kegTugas->status->label() }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="detail-desc detail-empty">Belum ada tugas yang ditautkan ke kegiatan ini.</div>
                    @endif
                </section>
            </div>

            <div class="modal-footer detail-footer">
                @if($isKabag)
                <button type="button" 
                        class="detail-delete" 
                        wire:click="deleteKegiatan({{ $detail_kegiatan->id }})" 
                        wire:confirm="Apakah Anda yakin ingin menghapus kegiatan ini?" 
                        wire:loading.attr="disabled">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                        <path d="M10 11v6" />
                        <path d="M14 11v6" />
                        <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                    </svg>
                    Hapus
                </button>
                @endif

                <div class="detail-footer-actions">
                    <button type="button" class="btn btn-secondary" wire:click="closeDetailModal">Tutup</button>
                    @if($isKabag)
                    <button type="button" class="btn btn-secondary detail-edit" wire:click="openEditModal({{ $detail_kegiatan->id }})">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                        </svg>
                        Edit
                    </button>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- MODAL POPUP FORM TAMBAH / EDIT KEGIATAN (HANYA KABAG) --}}
    @if($isKabag)
    <div
        class="modal-backdrop {{ $show_form_modal ? 'open' : '' }}"
        x-data
        @keydown.escape.window="$wire.closeFormModal()"
        @if(!$show_form_modal) style="display: none;" @endif>
        <div class="modal-box" style="max-width: 620px;">
            <div class="modal-header">
                <h3 class="modal-title">
                    {{ $form_kegiatan_id ? 'Edit Kegiatan' : 'Tambah Kegiatan Baru' }}
                </h3>
                <button type="button" class="modal-close" wire:click="closeFormModal" aria-label="Tutup">
                    <x-icon name="x" :size="20" />
                </button>
            </div>

            <form wire:submit.prevent="simpanKegiatan">
                <div class="modal-body" style="display: flex; flex-direction: column; gap: 16px;">
                    {{-- Tombol Tambah dari Google Calendar (hanya saat tambah, bukan edit) --}}
                    @if(! $form_kegiatan_id)
                    <div style="padding: 12px 16px; background-color: var(--green-50); border: 1px solid var(--border); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--green-700)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                <line x1="16" y1="2" x2="16" y2="6" />
                                <line x1="8" y1="2" x2="8" y2="6" />
                                <line x1="3" y1="10" x2="21" y2="10" />
                            </svg>
                            @if($form_from_gcal)
                            <span style="font-size: 0.8125rem; color: var(--success); font-weight: 600;">
                                Terisi dari Google Calendar
                            </span>
                            @else
                            <span style="font-size: 0.8125rem; color: var(--muted);">
                                Impor jadwal dari Google Calendar
                            </span>
                            @endif
                        </div>
                        @if(! $form_from_gcal)
                        <a
                            href="{{ route('kabag.google-calendar.redirect') }}"
                            class="btn btn-secondary"
                            style="height: 32px; padding: 0 14px; font-size: 0.8125rem; text-decoration: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                                <polyline points="15 3 21 3 21 9" />
                                <line x1="10" y1="14" x2="21" y2="3" />
                            </svg>
                            Tambah dari Google Calendar
                        </a>
                        @endif
                    </div>
                    @endif

                    {{-- Judul --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="form_judul" class="form-label">Judul Kegiatan *</label>
                        <input
                            type="text"
                            id="form_judul"
                            wire:model="form_judul"
                            class="form-control {{ $errors->has('form_judul') ? 'is-invalid' : '' }}"
                            placeholder="Contoh: Liputan Kunjungan Kerja Menteri">
                        @error('form_judul')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Tanggal & Waktu --}}
                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 12px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="form_tanggal" class="form-label">Tanggal *</label>
                            <input
                                type="date"
                                id="form_tanggal"
                                wire:model="form_tanggal"
                                class="form-control {{ $errors->has('form_tanggal') ? 'is-invalid' : '' }}">
                            @error('form_tanggal')
                            <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="form_waktu_mulai" class="form-label">Mulai</label>
                            <input
                                type="time"
                                id="form_waktu_mulai"
                                wire:model="form_waktu_mulai"
                                class="form-control {{ $errors->has('form_waktu_mulai') ? 'is-invalid' : '' }}"
                                title="Kosongkan jika seharian">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="form_waktu_selesai" class="form-label">Selesai</label>
                            <input
                                type="time"
                                id="form_waktu_selesai"
                                wire:model="form_waktu_selesai"
                                class="form-control {{ $errors->has('form_waktu_selesai') ? 'is-invalid' : '' }}">
                        </div>
                    </div>
                    @error('form_waktu_selesai')
                    <span class="form-error" style="margin-top: -8px;">{{ $message }}</span>
                    @enderror

                    {{-- Tempat --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="form_tempat" class="form-label">Tempat / Lokasi *</label>
                        <input
                            type="text"
                            id="form_tempat"
                            wire:model="form_tempat"
                            class="form-control {{ $errors->has('form_tempat') ? 'is-invalid' : '' }}"
                            placeholder="Contoh: Ruang Rapat Gedung A Lantai 2">
                        @error('form_tempat')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="form_deskripsi" class="form-label">Deskripsi (Opsional)</label>
                        <textarea
                            id="form_deskripsi"
                            wire:model="form_deskripsi"
                            class="form-control"
                            rows="3"
                            placeholder="Tambahkan catatan atau detail kegiatan..."
                            style="height: auto; padding: 10px 14px;"></textarea>
                    </div>

                    {{-- Pemilihan PIC (Multi-pilih + Tombol Pilih Semua) --}}
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <label class="form-label" style="margin-bottom: 0;">
                                Penanggung Jawab (PIC)
                            </label>
                            <button
                                type="button"
                                wire:click="toggleSelectAllPics"
                                class="btn btn-secondary"
                                style="height: 28px; padding: 0 10px; font-size: 0.75rem;">
                                {{ count($form_pic_ids) === $availablePics->count() && $availablePics->isNotEmpty() ? 'Batalkan Semua' : 'Pilih Semua' }}
                            </button>
                        </div>

                        <div style="max-height: 160px; overflow-y: auto; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 12px; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; background-color: var(--surface);">
                            @forelse($availablePics as $p)
                            <label class="custom-checkbox" style="gap: 8px;">
                                <input
                                    type="checkbox"
                                    value="{{ $p->id }}"
                                    wire:model="form_pic_ids">
                                <span style="font-size: 0.875rem;">{{ $p->name }}</span>
                            </label>
                            @empty
                            <span style="color: var(--muted); font-size: 0.8125rem;">Tidak ada anggota staff/intern aktif.</span>
                            @endforelse
                        </div>
                    </div>

                    {{-- Tombol Tambah Tugas & Area Form Tugas Mengembang ke Bawah (Fase 5) --}}
                    @if(! $form_kegiatan_id)
                    <div style="border-top: 1px dashed var(--border); padding-top: 16px; margin-top: 8px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                            <div>
                                <span class="form-label" style="margin-bottom: 2px;">Tugas Terkait</span>
                                <p style="font-size: 0.8125rem; color: var(--muted); margin: 0;">
                                    Tugaskan pekerjaan untuk kegiatan ini (disimpan bersama dalam satu transaksi).
                                </p>
                            </div>
                            <button type="button" class="btn btn-secondary" style="height: 32px; font-size: 0.8125rem;" wire:click="addDraftTask">
                                <x-icon name="plus" :size="14" />
                                <span>Tambah Tugas</span>
                            </button>
                        </div>

                        @if(! empty($draft_tasks))
                        <div class="expand-section" style="display: flex; flex-direction: column; gap: 16px; margin-top: 12px;">
                            @foreach($draft_tasks as $index => $draftTask)
                            <div style="padding: 16px; background-color: var(--green-50); border: 1px solid var(--border); border-radius: var(--radius-sm); position: relative;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
                                    <span style="font-size: 0.875rem; font-weight: 700; color: var(--green-900);">
                                        Tugas #{{ $index + 1 }}
                                    </span>
                                    <button type="button" class="btn btn-danger" style="height: 26px; padding: 0 8px; font-size: 0.75rem;" wire:click="removeDraftTask({{ $index }})">
                                        Hapus Tugas
                                    </button>
                                </div>

                                <x-tugas.form-fields
                                    :pics="$availablePics"
                                    :prefix="'draft_tasks.' . $index . '.'"
                                    :hide-kegiatan="true" />
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeFormModal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ $form_kegiatan_id ? 'Simpan Perubahan' : 'Tambah Kegiatan' }}</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL KONFIRMASI HAPUS (HANYA KABAG) --}}
    <div
        class="modal-backdrop {{ $show_delete_modal ? 'open' : '' }}"
        x-data
        @keydown.escape.window="$wire.closeDeleteModal()"
        @if(!$show_delete_modal) style="display: none;" @endif>
        <div class="modal-box" style="max-width: 440px;">
            <div class="modal-header">
                <h3 class="modal-title">Hapus Kegiatan</h3>
                <button type="button" class="modal-close" wire:click="closeDeleteModal" aria-label="Tutup">
                    <x-icon name="x" :size="20" />
                </button>
            </div>

            <div class="modal-body">
                <p style="font-size: 0.9375rem; color: var(--text);">
                    Apakah Anda yakin ingin menghapus kegiatan <strong>"{{ $delete_kegiatan_judul }}"</strong>?
                </p>
                <p style="font-size: 0.8125rem; color: var(--muted); margin-top: 8px;">
                    Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">
                    Batal
                </button>
                <button type="button" class="btn btn-danger" wire:click="deleteKegiatan" wire:loading.attr="disabled">
                    Ya, Hapus Kegiatan
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL POPUP EXPORT PDF (SEMUA ROLE DI TAB RIWAYAT) --}}
    <div
        class="modal-backdrop {{ $show_export_modal ? 'open' : '' }}"
        x-data
        @keydown.escape.window="$wire.closeExportModal()"
        @if(!$show_export_modal) style="display: none;" @endif>
        <div class="modal-box" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title">Export PDF Riwayat Kegiatan</h3>
                <button type="button" class="modal-close" wire:click="closeExportModal" aria-label="Tutup">
                    <x-icon name="x" :size="20" />
                </button>
            </div>

            <form wire:submit.prevent="exportPdf">
                <div class="modal-body" style="display: flex; flex-direction: column; gap: 18px;">
                    @if($export_error)
                    <div class="alert alert-danger" style="margin-bottom: 0;">
                        <span>{{ $export_error }}</span>
                    </div>
                    @endif

                    {{-- Pilihan Mode Export (Berdasarkan Bulan vs Rentang Tanggal) --}}
                    <div>
                        <label class="form-label">Metode Pemilihan Periode</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; background-color: var(--green-50); padding: 4px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                            <button
                                type="button"
                                wire:click="setExportMode('bulan')"
                                class="btn {{ $export_mode === 'bulan' ? 'btn-primary' : '' }}"
                                style="height: 34px; font-size: 0.8125rem; {{ $export_mode !== 'bulan' ? 'background: transparent; color: var(--text);' : '' }}">
                                Berdasarkan Bulan
                            </button>
                            <button
                                type="button"
                                wire:click="setExportMode('rentang')"
                                class="btn {{ $export_mode === 'rentang' ? 'btn-primary' : '' }}"
                                style="height: 34px; font-size: 0.8125rem; {{ $export_mode !== 'rentang' ? 'background: transparent; color: var(--text);' : '' }}">
                                Rentang Tanggal
                            </button>
                        </div>
                    </div>

                    {{-- Form Input: Berdasarkan Bulan (Transisi Halus) --}}
                    @if($export_mode === 'bulan')
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px; animation: toastSlideIn 200ms var(--ease);">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="export_month" class="form-label">Bulan</label>
                            <select id="export_month" wire:model="export_month" class="form-control">
                                @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}">
                                    {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="export_year" class="form-label">Tahun</label>
                            <input
                                type="number"
                                id="export_year"
                                wire:model="export_year"
                                class="form-control"
                                min="2020"
                                max="2099">
                        </div>
                    </div>
                    @else
                    {{-- Form Input: Berdasarkan Rentang Tanggal (Transisi Halus) --}}
                    <div style="display: flex; flex-direction: column; gap: 12px; animation: toastSlideIn 200ms var(--ease);">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="export_start_date" class="form-label">Dari Tanggal *</label>
                                <input
                                    type="date"
                                    id="export_start_date"
                                    wire:model="export_start_date"
                                    class="form-control {{ $errors->has('export_start_date') ? 'is-invalid' : '' }}">
                                @error('export_start_date')
                                <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label for="export_end_date" class="form-label">Sampai Tanggal *</label>
                                <input
                                    type="date"
                                    id="export_end_date"
                                    wire:model="export_end_date"
                                    max="{{ \Carbon\Carbon::now('Asia/Jakarta')->subDay()->format('Y-m-d') }}"
                                    class="form-control {{ $errors->has('export_end_date') ? 'is-invalid' : '' }}">
                                @error('export_end_date')
                                <span class="form-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--muted);">
                            * Tanggal akhir dibatasi maksimal hari kemarin (kegiatan sebelum hari ini WIB).
                        </span>
                    </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeExportModal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Unduh Dokumen PDF</span>
                        <span wire:loading>Membuat PDF...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL TAMBAH TUGAS DARI POPUP RINCIAN KEGIATAN (HANYA KABAG) --}}
    @if($isKabag && $show_kegiatan_tugas_modal)
    <div class="modal-backdrop open" wire:click.self="closeKegiatanTugasModal">
        <div class="modal-box" style="max-width: 580px;">
            <div class="modal-header">
                <h2 class="modal-title">Tambah Tugas untuk Kegiatan</h2>
                <button type="button" class="modal-close" wire:click="closeKegiatanTugasModal" aria-label="Tutup">
                    <x-icon name="x" :size="20" />
                </button>
            </div>

            <form wire:submit.prevent="simpanKegiatanTugas">
                <div class="modal-body">
                    <x-tugas.form-fields
                        :pics="$availablePics"
                        prefix="kegiatan_tugas_"
                        :hide-kegiatan="true"
                        :kegiatan-judul="$detail_kegiatan?->judul" />
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeKegiatanTugasModal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>Simpan Tugas</span>
                        <span wire:loading>Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- MODAL POPUP PILIH EVENT GOOGLE CALENDAR (HANYA KABAG) --}}
    @if($isKabag)
    <div
        class="modal-backdrop {{ $show_gcal_picker ? 'open' : '' }}"
        x-data
        @keydown.escape.window="$wire.closeGcalPicker()"
        @if(!$show_gcal_picker) style="display: none;" @endif>
        <div class="modal-box gcal-modal">
            <div class="modal-header gcal-header">
                <div class="gcal-header-main">
                    <span class="gcal-header-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="modal-title gcal-title">Pilih Event dari Google Calendar</h3>
                        <p class="gcal-subtitle">Sinkronisasi kegiatan internal secara terstruktur</p>
                    </div>
                </div>
                <button type="button" class="modal-close gcal-close" wire:click="closeGcalPicker" aria-label="Tutup">
                    <x-icon name="x" :size="18" />
                </button>
            </div>

            <div class="modal-body gcal-body">
                <div class="gcal-info">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="16" x2="12" y2="12" />
                        <line x1="12" y1="8" x2="12.01" y2="8" />
                    </svg>
                    <span>Hanya event 6 bulan ke depan yang ditampilkan.</span>
                </div>

                @php
                $gcalTime = fn ($t) => str_replace(':', '.', (string) $t);
                @endphp

                <div class="gcal-list">
                    @forelse($gcal_events as $idx => $event)
                    @php
                    $isAlreadyAdded = in_array($event['id'], $importedGcalIds ?? [], true);
                    $isSelected = $selected_gcal_index !== null && (string) $selected_gcal_index === (string) $idx;
                    $waktuLabel = !empty($event['waktu_mulai'])
                    ? $gcalTime($event['waktu_mulai']) . (!empty($event['waktu_selesai']) ? '–' . $gcalTime($event['waktu_selesai']) : '') . ' WIB'
                    : 'Seharian';
                    @endphp
                    <label
                        wire:key="gcal-event-{{ $event['id'] }}"
                        class="gcal-option {{ $isSelected ? 'is-selected' : '' }} {{ $isAlreadyAdded ? 'is-disabled' : '' }}">
                        <input
                            type="radio"
                            name="gcal_selection_radio"
                            value="{{ $idx }}"
                            class="gcal-radio"
                            wire:model.live="selected_gcal_index"
                            @if($isAlreadyAdded) disabled @endif>

                        <span class="gcal-option-content">
                            <span class="gcal-option-top">
                                <span class="gcal-option-title">{{ $event['judul'] }}</span>
                                @if($isAlreadyAdded)
                                <span class="gcal-added-badge">Sudah ditambahkan</span>
                                @endif
                            </span>

                            <span class="gcal-option-meta">
                                <span class="gcal-meta-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                    {{ \Carbon\Carbon::parse($event['tanggal'], 'Asia/Jakarta')->translatedFormat('l, j M Y') }}
                                    <span class="gcal-sep">&bull;</span>
                                    {{ $waktuLabel }}
                                </span>

                                @if(!empty($event['tempat']))
                                <span class="gcal-meta-item gcal-meta-place">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                    {{ \Illuminate\Support\Str::limit($event['tempat'], 60) }}
                                </span>
                                @endif
                            </span>
                        </span>
                    </label>
                    @empty
                    <div class="gcal-empty">Tidak ada event Google Calendar yang ditemukan.</div>
                    @endforelse
                </div>
            </div>

            <div class="modal-footer gcal-footer">
                @if(!empty($gcal_account_email ?? null))
                <span class="gcal-account">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="23 4 23 10 17 10" />
                        <polyline points="1 20 1 14 7 14" />
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" />
                    </svg>
                    Akun Google: <strong>{{ $gcal_account_email }}</strong>
                </span>
                @endif

                <div class="gcal-footer-actions">
                    <button type="button" class="btn btn-secondary" wire:click="closeGcalPicker">Batal</button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        wire:click="confirmGcalSelection"
                        @if($selected_gcal_index===null || (isset($gcal_events[$selected_gcal_index]) && in_array($gcal_events[$selected_gcal_index]['id'], $importedGcalIds ?? [], true))) disabled @endif>
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>