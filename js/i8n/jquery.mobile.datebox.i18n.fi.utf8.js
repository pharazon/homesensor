/*
 * jQuery Mobile Framework : plugin to provide a date and time picker.
 * Copyright (c) JTSage
 * CC 3.0 Attribution.  May be relicensed without permission/notifcation.
 * https://github.com/jtsage/jquery-mobile-datebox
 *
 * Translation by: Ville Salonen <ville.salonen@iki.fi>
 *
 */

jQuery.extend(jQuery.mobile.datebox.prototype.options.lang, {
	'fi': {
		setDateButtonLabel: "Valitse päivä",
		setTimeButtonLabel: "Valitse aika",
		setDurationButtonLabel: "Valitse kesto",
		calTodayButtonLabel: "Tänään",
		titleDateDialogLabel: "Valitse päivämäärä",
		titleTimeDialogLabel: "Valitse aika",
		daysOfWeek: ["Sunnuntai", "Maanantai", "Tiistai", "Keskiviikko", "Torstai", "Perjantai", "Lauantai"],
		daysOfWeekShort: ["Su", "Ma", "Ti", "Ke", "To", "Pe", "La"],
		monthsOfYear: ["Tammikuu", "Helmikuu", "Maaliskuu", "Huhtikuu", "Toukokuu", "Kesäkuu", "Heinäkuu", "Elokuu", "Syyskuu", "Lokakuu", "Marraskuu", "Joulukuu"],
		monthsOfYearShort: ["Tammi", "Helmi", "Maali", "Huhti", "Touko", "Kesä", "Heinä", "Elo", "Syys", "Loka", "Marras", "Joulu"],
		durationLabel: ["Päivää", "Tuntia", "Minuuttia", "Sekuntia"],
		durationDays: ["Päivä", "Päivää"],
		tooltip: "Avaa päivämäärävalitsin",
		nextMonth: "Seuraava kuukausi",
		prevMonth: "Edellinen kuukausi",
		timeFormat: 24,
		headerFormat: '%A, %B %-d, %Y',
		dateFieldOrder: ['d','m','y'],
		timeFieldOrder: ['h', 'i', 'a'],
		slideFieldOrder: ['y', 'm', 'd'],
		dateFormat: "%d.%m.%Y",
		useArabicIndic: false,
		isRTL: false,
		calStartDay: 0,
		clearButton: "Selkeää",
		durationOrder: ['d', 'h', 'i', 's'],
		meridiem: ["AM", "PM"],
		timeOutput: "%k:%M",
		durationFormat: "%Dd %DA, %Dl:%DM:%DS"
	}
});
jQuery.extend(jQuery.mobile.datebox.prototype.options, {
	useLang: 'fi'
});

