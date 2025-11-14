<?php
require_once 'db_connect.php'; 

// Security Check: Only allow logged-in Doctors
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: index.php?error=access_denied");
    exit();
}
$doctor_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Calendar</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin-top: 20px;
            border: 1px solid #b3e5fc;
            border-radius: 8px;
            padding: 10px;
        }
        .day-header {
            font-weight: 600;
            color: #0077b6;
            text-align: center;
            padding: 8px 0;
            border-bottom: 2px solid #b3e5fc;
        }
        .day-cell {
            min-height: 80px;
            border: 1px solid #f0ffff;
            background-color: #f5faff;
            padding: 5px;
            font-size: 0.9em;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .day-cell:hover {
            background-color: #e0f7fa;
        }
        .current-day {
            border: 2px solid #ff6b81;
            background-color: #ffebee;
        }
        .event {
            background-color: #ff8c00;
            color: white;
            border-radius: 4px;
            padding: 2px 5px;
            margin-top: 3px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .event-form-area {
            max-width: 400px;
            margin: 40px 0;
            padding: 20px;
            border: 1px solid #b3e5fc;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container full-screen">
        <div class="header-bar" style="
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            padding: 40px 20px;
            ">
            
            <div style="grid-column: 1 / 2; justify-self: start;">
                <span class="logo-circle" style="font-weight: 600;">NK</span>
            </div>
            
            <h1 style="
                color: white; 
                font-weight: 600; 
                margin: 0;
                grid-column: 2 / 3;
                ">
                Doctor Event Calendar
            </h1>
            
            <div id="live-clock-display" class="live-clock" style="grid-column: 3 / 4; justify-self: end;"></div>
        </div>
        
        <div class="centered-content">
            <h2>Schedule Planner</h2>

            <p><a href="doctor_dashboard.php" class="button" style="background-color:#0077b6;">&larr; Back to Dashboard</a></p>

            <!-- Calendar Display -->
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <button onclick="changeMonth(-1)">&#9664; Prev</button>
                <h3 id="current-month-year"></h3>
                <button onclick="changeMonth(1)">Next &#9654;</button>
            </div>
            
            <div id="calendar" class="calendar-grid">
                <!-- Calendar will be populated by JavaScript -->
            </div>
            
            <!-- Event Submission Form -->
            <div class="event-form-area">
                <h3>Add Personal Event</h3>
                <form id="event-form">
                    <label for="event-date">Date:</label>
                    <input type="date" id="event-date" required>
                    
                    <label for="event-desc">Description (e.g., Seminar, Holiday):</label>
                    <input type="text" id="event-desc" required>

                    <button type="submit">Save Event</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        const DOCTOR_ID = <?php echo $doctor_id; ?>;
        let currentMonth = new Date().getMonth();
        let currentYear = new Date().getFullYear();

        // Use localStorage to simulate persistent events (since Firestore is not used)
        function getEventsKey() {
            // Ensure events are stored per doctor
            return `doctor_events_${DOCTOR_ID}`;
        }
        function loadEvents() {
            try {
                // Return the whole object of events keyed by date
                return JSON.parse(localStorage.getItem(getEventsKey())) || {};
            } catch (e) {
                console.error("Error loading events from storage:", e);
                return {};
            }
        }
        function saveEvents(events) {
            localStorage.setItem(getEventsKey(), JSON.stringify(events));
        }

        // --- Calendar Rendering Logic ---
        function renderCalendar(month, year) {
            const calendarEl = document.getElementById('calendar');
            const monthYearEl = document.getElementById('current-month-year');
            
            // Corrected: Month input needs to be 0-indexed for JavaScript Date object
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = new Date();
            const currentEvents = loadEvents();
            
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            const dayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

            monthYearEl.textContent = `${monthNames[month]} ${year}`;
            calendarEl.innerHTML = ''; 

            // Add headers
            dayNames.forEach(name => {
                const header = document.createElement('div');
                header.className = 'day-header';
                header.textContent = name;
                calendarEl.appendChild(header);
            });

            // Fill leading empty cells
            for (let i = 0; i < firstDay; i++) {
                calendarEl.appendChild(document.createElement('div'));
            }

            // Fill days
            for (let day = 1; day <= daysInMonth; day++) {
                // Date key format: YYYY-MM-DD
                const dateKey = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const cell = document.createElement('div');
                cell.className = 'day-cell';
                cell.dataset.date = dateKey;
                
                // Highlight current day
                if (day === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                    cell.classList.add('current-day');
                }
                
                cell.innerHTML = `<strong>${day}</strong>`;

                // Add events for this day
                if (currentEvents[dateKey]) {
                    // Filter events to ensure only the current doctor's events are shown (optional if keys are unique)
                    currentEvents[dateKey].filter(event => event.doctorId == DOCTOR_ID).forEach(event => {
                        const eventEl = document.createElement('div');
                        eventEl.className = 'event';
                        eventEl.textContent = event.desc;
                        cell.appendChild(eventEl);
                    });
                }
                
                // Add event listener to auto-populate form date
                cell.addEventListener('click', () => {
                    document.getElementById('event-date').value = dateKey;
                });
                
                calendarEl.appendChild(cell);
            }
        }

        function changeMonth(step) {
            currentMonth += step;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            } else if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            // Check if month or year changed, then re-render
            renderCalendar(currentMonth, currentYear);
        }

        // --- Event Submission Logic ---
        document.getElementById('event-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const date = document.getElementById('event-date').value;
            const desc = document.getElementById('event-desc').value.trim();

            if (!date || !desc) {
                // Use a custom message box instead of alert()
                console.error("Please select a date and enter a description.");
                return;
            }

            const allEvents = loadEvents();
            if (!allEvents[date]) {
                allEvents[date] = [];
            }
            
            // Save event with the current DOCTOR_ID
            allEvents[date].push({ desc: desc, doctorId: DOCTOR_ID });
            saveEvents(allEvents);
            
            // Clear form and re-render
            document.getElementById('event-desc').value = '';
            
            // Corrected: Pass the correct month/year indices to re-render the currently viewed calendar
            renderCalendar(currentMonth, currentYear);
        });


        // Initial load
        document.addEventListener('DOMContentLoaded', () => {
            renderCalendar(currentMonth, currentYear);
        });
    </script>
</body>
</html>