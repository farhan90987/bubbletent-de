
function search_radius_style(){
    const range = document.getElementById('search-radius');
    if(range){
        const setRangeFill = () => {
            const min = parseInt(range.min) || 0;
            const max = parseInt(range.max) || 100;
            const val = parseInt(range.value) || 0;
            const percent = ((val - min) * 100) / (max - min);
            range.style.background = `linear-gradient(to right, #4d7359 0%, #4d7359 ${percent}%, #e0e0e0 ${percent}%, #e0e0e0 100%)`;
        };

        setRangeFill();
        range.addEventListener('input', setRangeFill);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    search_radius_style();
})