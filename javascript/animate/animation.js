import { animate, inView } from "motion";

export function animation() {

    inView(".fade-up", () => {
        animate(".fade-up", { opacity: [0, 1], y: [100, 0] }, { duration:.5 })
    })
}