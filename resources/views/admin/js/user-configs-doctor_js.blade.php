<script type="module">
    const sunday = document.getElementById("sunday");
    const monday = document.getElementById("monday");
    const tuesday = document.getElementById("tuesday");
    const wednesday = document.getElementById("wednesday");
    const thursday = document.getElementById("thursday");
    const friday = document.getElementById("friday");
    const saturday = document.getElementById("saturday");

    let userData;
    let daySelector;
    let startTimePicker;
    let endTimePicker;

    const clearPicker = () => {
        $('#add-schedule-btn').prop('disabled', true);
        startTimePicker.dates.setValue(null);
        endTimePicker.dates.setValue(null);
    }

    const addSchedule = (e) => {
        const schedule = localStorage.getItem('schedule');
        let sch;
        // Update existing storage or create new list
        if (schedule) {
            sch = JSON.parse(schedule);
        } else {
            sch = {
                sunday: [],
                monday: [],
                tuesday: [],
                wednesday: [],
                thursday: [],
                friday: [],
                saturday: []
            }
        }

        for (let d in sch) {
            if (d === daySelector.getValue()) {
                sch[d].push({
                    start: `${zeroPad(startTimePicker.dates.lastPicked.hours)}:${zeroPad(startTimePicker.dates.lastPicked.minutes)}`,
                    end: `${zeroPad(endTimePicker.dates.lastPicked.hours)}:${zeroPad(endTimePicker.dates.lastPicked.minutes)}`
                })
            }
        }

        localStorage.setItem('schedule', JSON.stringify(sch));

        updatePreview(sch);
        clearPicker();
    }

    const zeroPad = (num) => String(num).padStart(2, '0');

    const updatePreview = (currentSch = null) => {
        let sch;

        if (currentSch) {
            sch = currentSch;
        } else {
            sch = {
                sunday: [],
                monday: [],
                tuesday: [],
                wednesday: [],
                thursday: [],
                friday: [],
                saturday: []
            }
        }

        for (let day in sch) {
            if (sch[day].length > 0) {
                let html = '';
                for (let s in sch[day]) {
                    html += `
                    <button
                        data-sch-day="${day}"
                        data-sch-idx="${s}"
                        onclick="window.individualSchDelete(${day}, ${s})"
                        style="--bs-btn-padding-y: .1rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem; --bs-btn-border-radius: 50px"
                        class="btn btn-sm btn-primary"
                    >
                        ${zeroPad(sch[day][s].start)} - ${zeroPad(sch[day][s].end)}
                        <i class="ms-2 bi bi-x-circle-fill"></i>
                    </button>`;
                }
                document.getElementById(day).innerHTML = html;
            } else {
                document.getElementById(day).innerHTML = null;
            }
        }
    }

    const daySelectChange = (val) => {
        const schedule = localStorage.getItem('schedule');
        let parsedSchedule = JSON.parse(schedule);

        if (schedule) {
            for (let day in parsedSchedule) {
                if (day === val) {
                    // console.log(parsedSchedule[day]);
                }
            }
        }
    }

    const individualSchDelete = (day, idx) => {
        const schedule = localStorage.getItem('schedule');
        let parsedSchedule = JSON.parse(schedule);

        for (let schDay in parsedSchedule) {
            if (schDay === day.id) {
                parsedSchedule[schDay].splice(idx, 1);
            }
        }

        localStorage.setItem('schedule', JSON.stringify(parsedSchedule));
        updatePreview(parsedSchedule);
    }

    window.zeroPad = zeroPad;
    window.individualSchDelete = individualSchDelete;

    $(document).ready(function() {
        let schedule;
        userData = JSON.parse('{!! $user !!}');
        if (userData.configs) {
            schedule = JSON.stringify(userData.configs.schedule);
            localStorage.setItem("schedule", schedule);
            updatePreview(userData.configs.schedule);
        }

        const $daySelector = $("#day-select").selectize({
            onChange: (e) => daySelectChange(e)
        });
        daySelector = $daySelector[0].selectize;
        startTimePicker = new TempusDominus(document.getElementById("start-time"), tDConfigsTime);
        endTimePicker = new TempusDominus(document.getElementById("end-time"), tDConfigsTime);

        const endOfHour = 23;
        const hours = [];
        for (let i = 0; i <= endOfHour; i++) {
            hours.push(i);
        }
        $("#start-time").on('change.td', function(e) {
            // Check if time schedule
            if (e.detail.date) {
                const hour = e.detail.date.hours;
                endTimePicker.updateOptions({
                    restrictions: {
                        disabledHours: hours.filter(h => h < hour)
                    }
                })
            }
        });
        $("#start-time").on('click.td', function(e) {
            startTimePicker.updateOptions({
                restrictions: {
                    minDate: moment().startOf('day').toDate(),
                    maxDate: moment().endOf('day').toDate()
                },
                defaultDate: moment().startOf('hour').toDate()
            })
        });

        $("#end-time").on('change.td', function(e) {
            if (endTimePicker.dates.lastPicked && startTimePicker.dates.lastPicked) {
                $('#add-schedule-btn').prop('disabled', false);
            } else {
                $('#add-schedule-btn').prop('disabled', true);
            }
        });
        $("#end-time").on('click.td', function(e) {
            endTimePicker.updateOptions({
                restrictions: {
                    minDate: moment().startOf('day').toDate(),
                    maxDate: moment().endOf('day').toDate()
                },
                defaultDate: moment().startOf('hour').toDate()
            })
        });

        $("#add-schedule-btn").on('click', addSchedule);
        $("#clear-schedule-btn").on('click', (e) => {
            localStorage.removeItem('schedule');
            updatePreview()
        });

        // $("[id^=individualSchBtn]").on('click', individualSchDelete);
    });
</script>