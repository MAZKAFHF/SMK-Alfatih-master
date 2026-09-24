import './bootstrap';
import './app.interactions';
import 'trix';
import 'trix/dist/trix.css';

// Trix: use <p> instead of <div> for school content — more semantic, matches purifier
document.addEventListener('DOMContentLoaded', () => {
    if (window.Trix) {
        window.Trix.config.blockAttributes.default.tagName = 'p';
        // Keep heading config as is, hide file tools already via CSS
    }
});
