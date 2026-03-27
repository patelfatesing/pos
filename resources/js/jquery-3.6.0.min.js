import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Alpine = Alpine;

Alpine.start();

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: "36ff0160ad86db49c893",
    cluster: "ap2",
    forceTLS: true,
});



// Listen for drawer opening event
window.Echo.channel("drawer-channel").listen(".drawer.opened", (event) => {
    console.log("Drawer open event received");

    // Prepare ESC/POS command for TVS drawer
    const config = qz.configs.create("Your_Printer_Name", {
        encoding: "UTF-8",
    });
    const data = [{ type: "raw", format: "hex", data: "1B700019FA" }]; // ESC/POS command to open drawer

    // Send to printer to open the drawer
    qz.print(config, data)
        .then(() => {
            console.log("Drawer opened!");
        })
        .catch((err) => {
            console.error("Error opening drawer:", err);
        });
});
