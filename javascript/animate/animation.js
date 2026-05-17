import { animate, inView } from "motion";

export function animation() {

    inView(".fade-up", () => {
        animate(".fade-up", { opacity: [0, 1], y: [100, 0] }, { duration:.5 })
    })
}


document.querySelectorAll("#rule .rule-item").forEach((element, i) => {
    // Set opacity awal ke 0
    element.style.opacity = "0";

    inView(element, () => {
        animate(
            element,
            { opacity: [0, 1], y: [40, 0] },
            {
                duration: 0.9,
                delay: i * 0.2,          // stagger 100ms per item
                easing: [0.22, 1, 0.36, 1],
            }
        );
    }, { margin: "0px 0px -80px 0px" });
});

document.querySelectorAll(".judge-item").forEach((element, i) => {
    // Set opacity awal ke 0
    element.style.opacity = "0";

    inView(element, () => {
        animate(
            element,
            { opacity: [0, 1], y: [40, 0] },
            {
                duration: 0.9,
                delay: i * 0.4,          // stagger 100ms per item
                easing: [0.22, 1, 0.36, 1],
            }
        );
    }, { margin: "0px 0px -80px 0px" });
});