

class MapMarkerManager {
    constructor() {
        this.inputMap = document.getElementById("input-map");
        this.inputContainer = document.getElementById("input-container");
        this.outputContainer = document.getElementById("output-container");
        this.clearButton = document.getElementById("clear-markers");
        this.markerCountDisplay = document.getElementById("marker-count");
        this.idSelect = document.getElementById("marker-id");
        this.regionId = document.getElementById("regionSelect");
        this.labelInput = document.getElementById("marker-label");
        this.markers = this.loadMarkers();
        this.dataList = this.loadDataList();
        this.initEventListeners();

        this.renderMarkers(this.inputContainer, true);
        this.renderMarkers(this.outputContainer, false);
        this.updateUI();
    }

    loadMarkers() {
        try {
            return JSON.parse(localStorage.getItem("markers")) || [];
        } catch (error) {
            console.error("Error loading markers from localStorage:", error);
            return [];
        }
    }



    saveMarkers() {
        try {
            localStorage.setItem("markers", JSON.stringify(this.markers));
        } catch (error) {
            console.error("Error saving markers to localStorage:", error);
        }
    }

    loadDataList(){
        try {
            return JSON.parse(localStorage.getItem("dataTitles")) || [];
        } catch (error) {
            console.error("Error loading dataTitles from localStorage:", error);
            return [];
        }
    }

    saveDataList() {
        try {
            localStorage.setItem("dataTitles", JSON.stringify(this.dataList));
        } catch (error) {
            console.error("Error saving markers to localStorage:", error);
        }
    }

    initEventListeners() {
        this.inputMap.addEventListener("load", () => {
            this.renderMarkers(this.inputContainer, true);
            this.renderMarkers(this.outputContainer, false);
        });

        this.inputContainer.addEventListener("click", (e) => {
            if (e.target.closest(".marker.removable")) {
                this.handleMarkerRemove(e);
            } else if (e.target === this.inputMap) {
                this.handleMapClick(e);
            }
        });

        this.clearButton.addEventListener("click", () => {
            if (confirm("Are you sure you want to clear all markers?")) {
                this.clearAllMarkers();
            }
        });

        if (this.regionId) {
            this.regionId.addEventListener('change', () => {
                this.renderMarkers(this.inputContainer, true);
                this.renderMarkers(this.outputContainer, false);
                this.updateUI()
            });
        }
    }

    handleMapClick(e) {
        const rect = this.inputMap.getBoundingClientRect();
        const xPercent = ((e.clientX - rect.left) / rect.width) * 100;
        const yPercent = ((e.clientY - rect.top) / rect.height) * 100;
        const dataId = this.idSelect.value;
        const regionId = this.regionId.value;

        const selectedOption = this.idSelect.selectedOptions[0];
        const dataName = selectedOption.dataset.name;

        if (!dataId) {
            alert("Please select a location before placing a marker.");
            return;
        }

        this.markers.push({ x: xPercent, y: yPercent, dataId, regionId, id: Date.now() });
        this.dataList.push({id: dataId, regionId, name: dataName})
        this.idSelect.value = "";
        this.saveMarkers();
        this.saveDataList();
        this.renderMarkers(this.inputContainer, true);
        this.renderMarkers(this.outputContainer, false);
        this.updateUI();
    }

    handleMarkerRemove(e) {
        const markerEl = e.target.closest(".marker");
        const markerId = parseInt(markerEl.dataset.id);

        const removedMarker = this.markers.find(m => m.id === markerId);
        const removedDataId = removedMarker ? removedMarker.dataId : null;

        this.markers = this.markers.filter(m => m.id !== markerId);

        if (removedDataId !== null) {
            this.dataList = this.dataList.filter(item => String(item.id) !== String(removedDataId));
        }


        this.saveMarkers();
        this.saveDataList();
        this.renderMarkers(this.inputContainer, true);
        this.renderMarkers(this.outputContainer, false);
        this.updateUI();
    }

    clearAllMarkers() {
        this.markers = [];
        this.dataList = [];
        this.saveMarkers();
        this.saveDataList();
        this.renderMarkers(this.inputContainer, true);
        this.renderMarkers(this.outputContainer, false);
        this.updateUI();
    }

    renderMarkers(container, removable = false) {
        container.querySelectorAll(".marker, .tooltip").forEach(el => el.remove());
        const selectedRegion = this.regionId ? this.regionId.value : "";

        this.markers
            .filter(marker => !selectedRegion || marker.regionId === selectedRegion)
            .forEach(({ x, y, id, dataId, regionId }) => {
                this.addMarkerTo(container, x, y, id, dataId, regionId, removable);
            });

    }

    addMarkerTo(container, x, y, id, dataId, regionId, removable) {
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
        const tooltip = tooltipTemplate.content.cloneNode(true).querySelector(".tooltip");

        tooltip.style.left = `${x}%`;
        tooltip.style.top = `${y - 1}%`;
        tooltip.style.display = "none";

        // Inject data
        const data = this.dataList.find(item => item.id === dataId && item.regionId === regionId) || {};
        tooltip.querySelector(".name").textContent = data.name || "Unknown";
        
        marker.addEventListener("mouseenter", () => {
            tooltip.style.display = "block";
        });
        marker.addEventListener("mouseleave", () => {
            tooltip.style.display = "none";
        });

        container.appendChild(tooltip);
    }

    updateUI() {
        const selectedRegion = this.regionId ? this.regionId.value : "";

        const visibleMarkers = this.markers.filter(marker => 
            !selectedRegion || marker.regionId === selectedRegion
        );

        this.markerCountDisplay.textContent = `Markers: ${visibleMarkers.length}`;
        this.clearButton.disabled = visibleMarkers.length === 0;
    }

}

function fetch_listing_by_region(){
    document.getElementById('regionSelect').addEventListener('change', function () {
        const termId = this.value;
        const selected = this.options[this.selectedIndex];
        const mapImage = selected.getAttribute('data-image');
        const imgTag = document.getElementById('input-map');
        const outPut = document.getElementById('output-map');

        toggleImage(imgTag, outPut, mapImage)

        fetch(`/wp-admin/admin-ajax.php?action=get_locations_by_region&term_id=${termId}`)
            .then(res => res.json())
            .then(data => {
                const locationSelect = document.getElementById('marker-id');
                locationSelect.innerHTML = '<option value="">Select a Location</option>';

                data.forEach(post => {
                    const opt = document.createElement('option');
                    opt.value = post.id;
                    opt.textContent = post.title;
                    opt.setAttribute('data-name', post.title);
                    locationSelect.appendChild(opt);
                });
            });
    });
}

function toggleImage(imgTag, outPut, mapImage) {
    if (!imgTag || !outPut) return;

    const imgParent = imgTag.closest('figure');
    const outParent = outPut.closest('figure');

    const show = mapImage && typeof mapImage === 'string' && mapImage.trim() !== '';

    if (show) {
        imgTag.src = mapImage;
        outPut.src = mapImage;

        imgTag.style.display = 'block';
        outPut.style.display = 'block';

    } else {
        imgTag.src = '';
        outPut.src = '';

        imgTag.style.display = 'none';
        outPut.style.display = 'none';
    }
}




function save_map_data(){
    document.getElementById('save-map-location').addEventListener('click', function (e) {
        e.preventDefault();

        const map_id = this.getAttribute('data-map-id');

        const button = this;
        const originalText = button.textContent;
        button.textContent = 'Saving...';

        console.log("map_id", map_id)

        const rawMapData  = localStorage.getItem('markers');
        const regionId = document.getElementById('regionSelect')?.value || 0;

        if (!rawMapData  || !regionId) {
            alert('Please select a region and ensure map data exists.');
            button.textContent = originalText;
            return;
        }

        let mapData;
        try {
            mapData = JSON.parse(rawMapData).filter(marker => marker.regionId == regionId);
        } catch (err) {
            alert('Invalid map data.');
            button.textContent = originalText;
            return;
        }

        if (mapData.length === 0) {
            alert('No markers found for the selected region.');
            button.textContent = originalText;
            return;
        }

        fetch(ajaxurl, {
            method: 'POST',
            body: new URLSearchParams({
                action: 'save_map_data',
                map_data: JSON.stringify(mapData),
                region_id: regionId,
                map_id: map_id,
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                alert('Map saved successfully!');
                //const remainingMarkers = allMarkers.filter(marker => marker.regionId != regionId);
                localStorage.setItem('markers', '');
                window.location.href = '/wp-admin/admin.php?page=mw-map-builder';
            } else {
                alert('Failed to save map.');
                button.textContent = originalText;

            }
        })
        .catch(err => {
            console.error('Error saving map data:', err);
            alert('Something went wrong, please again later!!');
            button.textContent = originalText;
        });

    });
}



document.addEventListener('DOMContentLoaded', function() {
    save_map_data();
    fetch_listing_by_region()

    setTimeout(function() {
        const loader = document.getElementById("map-loader");
        if (loader) loader.style.display = "none";
        const mapMarkerManager = new MapMarkerManager();
    }, 500)
})