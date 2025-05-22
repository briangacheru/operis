let inactivityTime = 0;
const maxInactivityTime = 3600; // 60 minutes
const countdownDuration = 120; // 2 minutes
let countdownInterval;
let countdownRemaining = countdownDuration;

// Reset inactivity time on user activity
document.addEventListener('mousemove', resetInactivityTime);
document.addEventListener('keydown', resetInactivityTime);
document.addEventListener('click', resetInactivityTime);

setInterval(() => {
    inactivityTime++;
    if (inactivityTime === maxInactivityTime) {
        showInactivityModal();
    }
}, 1000);

function resetInactivityTime() {
    inactivityTime = 0;
    closeInactivityModal();
}

function showInactivityModal() {
    const modal = document.getElementById('inactivityModal');
    modal.style.display = 'block';
    countdownRemaining = countdownDuration;
    document.getElementById('countdown').innerText = countdownRemaining;

    countdownInterval = setInterval(() => {
        countdownRemaining--;
        document.getElementById('countdown').innerText = countdownRemaining;

        if (countdownRemaining <= 0) {
            clearInterval(countdownInterval);
            logOut();
        }
    }, 1000);
}

function closeInactivityModal() {
    const modal = document.getElementById('inactivityModal');
    modal.style.display = 'none';
    clearInterval(countdownInterval);
}

function stayLoggedIn() {
    fetch('stay-logged-in.php', {method: 'POST'})
        .then(response => response.text())
        .then(data => console.log(data));
    resetInactivityTime();
}

function logOut() {
    fetch('logout.php?logout=1', {method: 'POST'})
        .then(response => window.location.href = 'logout.php');
}

function updateTime() {
    const timeDisplay = document.getElementById('timeDisplay');
    const now = new Date();
    const formattedTime = now.toLocaleTimeString(); // Get time in HH:MM:SS AM/PM format
    timeDisplay.textContent = formattedTime;
}

// Update the time every second
setInterval(updateTime, 1000);

// Initialize the time immediately on page load
updateTime();



