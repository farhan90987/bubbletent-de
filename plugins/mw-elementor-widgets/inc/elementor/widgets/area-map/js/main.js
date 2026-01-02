class MapMarkerManager {
        constructor(map_data, listing_data, id) {
            this.outputContainer = document.getElementById("mw-bubble-map-" + id);
            this.markers = map_data;
            this.dataList = listing_data
            this.renderMarkers(this.outputContainer, false);
        }

        renderMarkers(container, removable = false) {
            container.querySelectorAll(".marker, .mw-tooltip").forEach(el => el.remove());
            this.markers.forEach(({ x, y, id, dataId }) => {
                this.addMarkerTo(container, x, y, id, dataId, removable);
            });
        }

        addMarkerTo(container, x, y, id, dataId, removable) {
            const svgNS = "http://www.w3.org/2000/svg";
            const marker = document.createElementNS(svgNS, "svg");

            marker.setAttribute("width", "24");
            marker.setAttribute("height", "24");
            marker.setAttribute("viewBox", "0 0 24 24");
            marker.style.position = "absolute";
            marker.style.left = `${x}%`;
            marker.style.top = `${y}%`;
            marker.style.transform = `translate(-50%, -50%)`;
            marker.dataset.id = id;

            if (removable) {
                marker.classList.add("removable");
            } else {
                marker.style.pointerEvents = "auto";
            }

            const path = document.createElementNS(svgNS, "path");
            path.setAttribute("fill", "currentColor");
            path.setAttribute("fill-rule", "evenodd");
            path.setAttribute("clip-rule", "evenodd");
            path.setAttribute("d", "M11.291 21.706 12 21l-.709.706zM12 21l.708.706a1 1 0 0 1-1.417 0l-.006-.007-.017-.017-.062-.063a47.708 47.708 0 0 1-1.04-1.106 49.562 49.562 0 0 1-2.456-2.908c-.892-1.15-1.804-2.45-2.497-3.734C4.535 12.612 4 11.248 4 10c0-4.539 3.592-8 8-8 4.408 0 8 3.461 8 8 0 1.248-.535 2.612-1.213 3.87-.693 1.286-1.604 2.585-2.497 3.735a49.583 49.583 0 0 1-3.496 4.014l-.062.063-.017.017-.006.006L12 21zm0-8a3 3 0 1 0 0-6 3 3 0 0 0 0 6z");

            marker.appendChild(path);
            marker.classList.add("marker");
            container.appendChild(marker);

            // Clone tooltip template
            const tooltipTemplate = document.getElementById("tooltip-template");
            const tooltip = tooltipTemplate.content.cloneNode(true).querySelector(".mw-tooltip");

            tooltip.style.left = `50%`;
            tooltip.style.top = `50%`;
            marker.style.transform = `translate(-50%, -50%)`;
            tooltip.style.display = "none";

            // Inject data
            const data = this.dataList.find(item => item.id === dataId) || {};

            tooltip.querySelector(".name").textContent = data.name || "Unknown";
            tooltip.querySelector(".location").textContent = data.location || "";
            tooltip.querySelector(".price").textContent = data.price ? `${data.currency} ${data.price}` : "";
            tooltip.querySelector(".unit").textContent = data.unit || "Nacht";
            tooltip.querySelector(".date-range").textContent = data.date_range || "";
            const linkElement = tooltip.querySelector(".mw-tooltip-url");
            if (linkElement) {
                linkElement.href = (data.url && typeof data.url === 'string' && data.url.trim() !== '') 
                    ? data.url 
                    : '#';
            }

            // Optional image (if you provide image URL in dataList)
            if (data.image) {
                tooltip.querySelector(".image").src = data.image;
                tooltip.querySelector(".image").style.display = "block";
            } else {
                tooltip.querySelector(".image").style.display = "none";
            }

            
            // Prevent tooltip click from bubbling to document
            marker.addEventListener("click", (e) => e.stopPropagation());
            tooltipTemplate.addEventListener("click", (e) => e.stopPropagation());
            tooltip.addEventListener("click", (e) => e.stopPropagation());

            const outsideClickListener = (e) => {
                if (!tooltip.contains(e.target) && !marker.contains(e.target)) {
                    tooltip.style.display = "none";
                    document.removeEventListener("click", outsideClickListener);
                }
            };

            marker.addEventListener("click", (e) => {
                e.stopPropagation();

                document.querySelectorAll('.mw-tooltip').forEach(el => el.style.display = 'none');

                tooltip.style.display = "block";

                document.addEventListener("click", outsideClickListener);
            });

            container.appendChild(tooltip);
        }
    }
