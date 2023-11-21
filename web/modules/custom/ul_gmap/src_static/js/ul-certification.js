// source.js
import '../css/ul-certification.css';
import { startGlossary } from "./ul-glossary.js";
import { saveGlossaryFilters } from "./ul-glossary.js";
import { startCertificationSteps } from "./ul-cert-steps.js";
import { loadStepThree } from "./ul-cert-steps.js";
import { introAnimations } from "./ul-intro-page.js";

// console.log('ul certification js loaded!');

//Pass functions to trigger on twig files
window.startGlossary = startGlossary; //glossary-all

window.saveGlossaryFilters = saveGlossaryFilters; //glossary-all

window.startCertificationSteps = startCertificationSteps; //step-one

window.loadStepThree = loadStepThree; //step-one

window.introAnimations = introAnimations; //intro-page (index.php)
