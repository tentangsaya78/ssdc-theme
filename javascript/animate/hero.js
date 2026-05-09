import { animate, scale } from "motion";

export function animateHero() {

    const squence = [
        [
            ".hero-title",{
                opacity: [0, 1],
                y: [100, 0],
            }
        ],
        [
            ".hero-subtitle",{
                opacity: [0, 1],
                y: [100, 0],
            }
        ],
        [
         ".date",{
             opacity: [0, 1],
         }
        ],
        [
            ".aksen-date",{
                opacity: [0, 1],
                width: ["0%", "100%"],
            }
        ],
        [
            ".hero-location",{
                opacity: [0, 1],
                scale: [4, 1],
            }
        ],
        [
            ".hero-button",{
                opacity: [0, 1]
            }
        ],
        [
            ".hero-organizer",{
                opacity: [0, 1]
            }
        ]
    ]

    animate(squence, {
        duration: 3,
    });
  
}