<script type="module">
    // Elements
    const logoutButton = document.getElementById("logoutButton");

    const logoutHandler = async () => {
        const logout = await fetch("/api/v1/logout", {
            method: "post",
        });
        const resLogout = await logout.json();
        if (resLogout.status) {
            localStorage.removeItem("schedule");
            window.location = "/login";
        }
    };

    // Elements events
    if (logoutButton) {
        logoutButton.addEventListener("click", logoutHandler);
    }
</script>