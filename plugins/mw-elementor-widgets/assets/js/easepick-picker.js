function easepick_picker() {
    const DateTime = easepick.DateTime;

    const easepickLocales = {
        en: {
            months: ['January','February','March','April','May','June','July','August','September','October','November','December'],
            weekdays: ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
            weekdaysShort: ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
            format: 'DD-MM-YYYY',
            rangeTooltip: { one: 'night', other: 'nights' }
        },
        fr: {
            months: ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
            weekdays: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
            weekdaysShort: ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'],
            format: 'DD-MM-YYYY',
            rangeTooltip: { one: 'nuit', other: 'nuits' }
        },
        es: {
            months: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
            weekdays: ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'],
            weekdaysShort: ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'],
            format: 'DD-MM-YYYY',
            rangeTooltip: { one: 'noche', other: 'noches' }
        },
        bn: {
            months: ['জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'],
            weekdays: ['রবিবার','সোমবার','মঙ্গলবার','বুধবার','বৃহস্পতিবার','শুক্রবার','শনিবার'],
            weekdaysShort: ['রবি','সোম','মঙ্গল','বুধ','বৃহ','শুক্র','শনি'],
            format: 'DD-MM-YYYY',
            rangeTooltip: { one: 'রাত', other: 'রাত' }
        },
        de: {
            months: ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember'],
            weekdays: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
            weekdaysShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
            format: 'DD-MM-YYYY',
            rangeTooltip: { one: 'Nacht', other: 'Nächte' }
        }
    };

    const defaultLang = 'de';
    const wpmlLang = (typeof mySiteVars !== 'undefined' && mySiteVars.language) ? mySiteVars.language : defaultLang;
    const lang = easepickLocales[wpmlLang] ? wpmlLang : defaultLang;
    const locale = easepickLocales[lang];

    const isSmallDevice  = window.innerWidth <= 1920;
    const calendars = isSmallDevice ? 1 : 2;
    const grid = isSmallDevice ? 1 : 2;

    let bookedDates = mwewPluginData.mw_busy_dates || [];

    bookedDates = bookedDates.length ? bookedDates.map(d => {
        if (Array.isArray(d)) {
            const start = new DateTime(d[0], 'YYYY-MM-DD');
            const end = new DateTime(d[1], 'YYYY-MM-DD');
            return [start, end];
        }
        return new DateTime(d, 'YYYY-MM-DD');
    }) : [];

    const picker = new easepick.create({
        element: document.getElementById('mwew-checkin-date'),
        css: [
            'https://cdn.jsdelivr.net/npm/@easepick/bundle@1.2.1/dist/index.css',
            'https://easepick.com/css/demo_hotelcal.css',
        ],
        lang: lang,
        format: locale.format,
        calendars: calendars,
        grid: grid,
        plugins: ['RangePlugin'].concat(bookedDates.length ? ['LockPlugin'] : []),
        RangePlugin: {
            elementEnd: document.getElementById('mwew-checkout-date'),
            tooltipNumber(num) {
                return num - 1;
            },
            locale: locale.rangeTooltip,
        },
        ...(bookedDates.length ? {
            LockPlugin: {
                minDate: new Date(),
                minDays: 2,
                inseparable: true,
                filter(date, picked) {
                    // If nothing selected yet → block booked dates normally
                    if (picked.length === 0) {
                        return date.inArray(bookedDates, '[)');
                    }

                    // If start date selected
                    if (picked.length === 1) {
                        const start = picked[0];

                        // Allow same day
                        if (date.isSame(start, 'day')) {
                            return false;
                        }

                        // Check if date is blocked
                        const isBlocked = date.inArray(bookedDates, '[)');

                        if (!isBlocked) {
                            return false; // free date
                        }

                        // 🔥 IMPORTANT PART:
                        // Allow selecting the FIRST blocked day as checkout
                        const prevDay = date.clone().subtract(1, 'day');

                        return !prevDay.isSame(start, 'day');
                    }

                    // Default: block booked dates
                    return date.inArray(bookedDates, '[)');
                },
            }
        } : {}),
        i18n: {
            months: locale.months,
            weekdays: locale.weekdays,
            weekdaysShort: locale.weekdaysShort
        }
    });

    picker.on('select', () => {
        const start = picker.getStartDate();
        const end = picker.getEndDate();
        if (start && end) {
            setTimeout(() => {
                picker.hide();
            }, 100);
        }
    });

    const checkinWrapper = document.getElementById('checkin-wrapper');
    if (checkinWrapper) {
        checkinWrapper.addEventListener('click', () => {
            picker.show();
        });
    }

    const checkoutWrapper = document.getElementById('checkout-wrapper');
    if (checkoutWrapper) {
        checkoutWrapper.addEventListener('click', () => {
            picker.show();
        });
    }


}


document.addEventListener('DOMContentLoaded', function () {
    easepick_picker();
})