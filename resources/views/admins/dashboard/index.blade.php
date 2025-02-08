@extends('layouts.master', ['title' => 'Dashboard'])

@push('css')
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<style>
    /* Gradient background */
    .gradient-overlay {
        position: relative;
        background: linear-gradient(135deg, #6c5ce7, #a29bf5);
        padding: 2rem 0;
        border-radius: 12px;
        overflow: hidden;
    }

    /* Real-time clock styling */
    .realtime-clock {
        font-family: 'Poppins', sans-serif;
        font-size: 4rem;
        font-weight: bold;
        color: #ffffff;
        text-align: center;
        margin-bottom: 1rem;
        text-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        animation: pulseClock 2s infinite alternate;
    }

    /* Next prayer info styling */
    .next-prayer-info {
        font-family: 'Poppins', sans-serif;
        font-size: 1.5rem;
        color: #ffffff;
        text-align: center;
        margin-bottom: 1rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Location info styling */
    .location-info {
        font-family: 'Poppins', sans-serif;
        font-size: 1.2rem;
        color: #ffffff;
        text-align: center;
        margin-bottom: 1rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .location-info i {
        margin-right: 0.5rem;
    }

    /* Pulse animation for the clock */
    @keyframes pulseClock {
        0% {
            transform: scale(1);
        }

        100% {
            transform: scale(1.05);
        }
    }

    /* Prayer cards styling */
    .prayer-card-container {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
    }

    .prayer-card {
        background-color: #f8f9fa;
        border: 1px solid #ebedf3;
        border-radius: 12px;
        padding: 1.5rem;
        width: 150px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .prayer-card:hover {
        transform: translateY(-5px);
    }

    .prayer-card h6 {
        font-size: 1rem;
        font-weight: bold;
        color: #3f4254;
        margin-bottom: 0.5rem;
    }

    .prayer-card p {
        font-size: 1.2rem;
        color: #5e6278;
        margin: 0;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .prayer-card {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="toolbar" id="kt_toolbar">
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Dashboard</h1>
            </div>
        </div>
    </div>

    <!-- Gradient Overlay with Real-Time Clock -->
    <div class="row gy-5 g-xl-10 mt-8 mx-4">
        <div class="col-lg-12">
            <div class="gradient-overlay">
                <!-- Location Info -->
                <div class="location-info">
                    <i class="fas fa-map-marker-alt"></i> Lokasi: Demak
                </div>
                <!-- Real-Time Clock -->
                <div class="realtime-clock" id="realtime-clock"></div>
                <!-- Next Prayer Info -->
                <div class="next-prayer-info" id="next-prayer-info">Memuat informasi sholat...</div>
            </div>
        </div>
    </div>

    <!-- Prayer Cards -->
    <div class="row gy-5 g-xl-10 mt-4 mx-4">
        <div class="col-lg-12">
            <div class="prayer-card-container">
                @if(isset($prayerTimes) && !empty($prayerTimes))
                <div class="prayer-card">
                    <h6>Subuh</h6>
                    <p>{{ $prayerTimes['Fajr'] }}</p>
                </div>
                <div class="prayer-card">
                    <h6>Dzuhur</h6>
                    <p>{{ $prayerTimes['Dhuhr'] }}</p>
                </div>
                <div class="prayer-card">
                    <h6>Ashar</h6>
                    <p>{{ $prayerTimes['Asr'] }}</p>
                </div>
                <div class="prayer-card">
                    <h6>Maghrib</h6>
                    <p>{{ $prayerTimes['Maghrib'] }}</p>
                </div>
                <div class="prayer-card">
                    <h6>Isya</h6>
                    <p>{{ $prayerTimes['Isha'] }}</p>
                </div>
                @else
                <div class="prayer-card">
                    <p class="text-muted">Tidak dapat memuat jadwal sholat.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
    // Function to parse time in HH:mm format to Date object
    function parseTime(time) {
        const [hours, minutes] = time.split(':').map(Number);
        const now = new Date();
        const date = new Date(now);
        date.setHours(hours, minutes, 0, 0);
        return date;
    }

    // Function to calculate time difference using Moment.js
    function getTimeDifference(targetTime) {
        const now = moment(); // Current time
        const target = moment(targetTime); // Target prayer time
        if (target.isBefore(now)) {
            return { hours: 0, minutes: 0 }; // If the prayer time has passed
        }
        const duration = moment.duration(target.diff(now)); // Calculate the difference
        const hours = Math.floor(duration.asHours()); // Get hours
        const minutes = duration.minutes(); // Get remaining minutes
        return { hours, minutes };
    }

    // Function to find the next prayer
    function getNextPrayer(prayerTimes) {
        const now = new Date();
        const prayers = [
            { name: 'Subuh', time: parseTime(prayerTimes['Fajr']) },
            { name: 'Dzuhur', time: parseTime(prayerTimes['Dhuhr']) },
            { name: 'Ashar', time: parseTime(prayerTimes['Asr']) },
            { name: 'Maghrib', time: parseTime(prayerTimes['Maghrib']) },
            { name: 'Isya', time: parseTime(prayerTimes['Isha']) },
        ];

        // Find the next prayer today
        for (const prayer of prayers) {
            if (prayer.time > now) {
                return prayer;
            }
        }

        // If no prayer is found today, return the first prayer of the next day
        const tomorrow = new Date(now);
        tomorrow.setDate(tomorrow.getDate() + 1); // Move to the next day
        const firstPrayer = { ...prayers[0], time: new Date(tomorrow.setHours(0, 0, 0, 0)) };
        firstPrayer.time.setHours(...prayerTimes['Fajr'].split(':').map(Number)); // Set Subuh time for tomorrow
        return firstPrayer;
    }

    // Update the real-time clock and next prayer info
    function updateClockAndPrayerInfo(prayerTimes) {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('realtime-clock').textContent = `${hours}:${minutes}:${seconds}`;

        const nextPrayer = getNextPrayer(prayerTimes);
        const timeDifference = getTimeDifference(nextPrayer.time);

        if (timeDifference.hours === 0 && timeDifference.minutes === 0) {
            document.getElementById('next-prayer-info').textContent = `Sholat ${nextPrayer.name} sedang berlangsung.`;
        } else {
            const timeText = `${timeDifference.hours} jam ${timeDifference.minutes} menit lagi.`;
            document.getElementById('next-prayer-info').textContent = `Sholat ${nextPrayer.name} dalam ${timeText}`;
        }
    }

    // Initialize the clock and prayer info on page load
    document.addEventListener('DOMContentLoaded', () => {
        const prayerTimes = {
            Fajr: "{{ $prayerTimes['Fajr'] ?? '04:30' }}",
            Dhuhr: "{{ $prayerTimes['Dhuhr'] ?? '12:00' }}",
            Asr: "{{ $prayerTimes['Asr'] ?? '15:30' }}",
            Maghrib: "{{ $prayerTimes['Maghrib'] ?? '18:00' }}",
            Isha: "{{ $prayerTimes['Isha'] ?? '19:30' }}"
        };

        // Update every second
        setInterval(() => updateClockAndPrayerInfo(prayerTimes), 1000);

        // Initial update
        updateClockAndPrayerInfo(prayerTimes);
    });
</script>
@endpush
@endsection