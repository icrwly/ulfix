/**
 * SVGs for the Sustainability Report.
 * @Last Updated: 4/29/2021
 * @Last Ticket Worked: UL-3899
 */

(function ($, drupalSettings) {

  function elementInView(elem){
    return ($(window).height() + $(window).scrollTop()) > $(elem).offset().top;
  };

  function formatNumber(n, separator = ',') {
    n = n.toString()
    .split('').reverse().join('')
    .replace(/(\d{3})/g, '$1' + separator)
    .split('').reverse().join('')

    return n[0] == separator ? n.substr(1) : n
  }

  function loadGhgEmissions() {
    const emissionsObject = document.getElementById('statista-planet-ghg-emissions')
    emissionsObject.addEventListener('load', () => {
      const doc = emissionsObject.contentDocument

      TweenLite.set(doc.getElementById('emission_base'), { visibility: 'visible' })

      const counts = [
        { id: 'm1_txt', target: 36293.14, duration: 6 },
        { id: 'm2_txt', target: 28786.62, duration: 7 }
      ]

      counts.forEach((count) => {
        const elem = doc.getElementById(count.id)

        TweenLite.to({ val: 0 }, count.duration, {
          val: count.target, roundProps: { 'val': 0.01 },
          ease: Power3.easeOut, delay: 0.4,
          onUpdate: function() {
            elem.textContent = formatNumber(this.target.val.toFixed(2))
          }
        })
      })

      const timeline = new TimelineMax({ paused: true })

      const options = {
        transformOrigin: '50% 50%',
        alpha: 0,
        y: '+=100',
        ease: Expo.easeInOut
      }

      const animations = [
        { ids: ['m0', 'm5', 'dates'], options: { y: '+=200' }, stagger: 0, position: '+=0' },
        { ids: ['m1bar_1', 'm1bar_2'], options: { transformOrigin: '50% 100%' } },
        { ids: ['m1bar_1_txt', 'm1bar_2_txt',  'm1_total'] },
        { ids: ['m2bar_1', 'm2bar_2'], options: { transformOrigin: '50% 100%' } },
        { ids: ['m2bar_1_txt', 'm2bar_2_txt', 'm2_total'] },
        { ids: ['c1', 'c2', 'c3'], stagger: 0.1 },
        { ids: ['m4', 'm3'], stagger: 0.1 }
      ]

      animations.forEach((a) => {
        const elems = a.ids.map((i) => doc.getElementById(i))

        timeline.from(
          elems, 2.0,
          Object.assign(options, (a.options || {})),
          (a.stagger || 0.2), (a.position || '-=1.5')
        )
      })

      timeline.restart()
    })
  }

  function loadWaste() {
    const wasteObject = document.getElementById('statista-planet-waste')
    wasteObject.addEventListener('load', () => {
      const doc = wasteObject.contentDocument

      TweenLite.set(doc.getElementById('waste_base'), { visibility: 'visible' })

      const counts = [
        { id: 'm1_txt', target: 2417.10, duration: 6 },
        { id: 'm2_txt', target: 1696.15, duration: 7 },
        { id: 'm3_txt_1', target: 0.90, duration: 10 }
      ]

      counts.forEach((count) => {
        const elem = doc.getElementById(count.id)

        TweenLite.to({ val: 0 }, count.duration, {
          val: count.target, roundProps: { 'val': 0.01 },
          ease: Power3.easeOut, delay: 0.4,
          onUpdate: function() {
            elem.textContent = formatNumber(this.target.val.toFixed(2))
          }
        })
      })

      const timeline = new TimelineMax({ paused: true })

      const options = {
        transformOrigin: '50% 50%',
        alpha: 0,
        y: '+=100',
        ease: Expo.easeInOut
      }

      const animations = [
        { ids: ['m0', 'm5', 'dates'], options: { y: '+=200' }, stagger: 0, position: '+=0' },
        { ids: ['m1bar_1', 'm1bar_2', 'm1bar_3'], options: { transformOrigin: '50% 100%' } },
        { ids: ['m1bar_1_txt', 'm1bar_2_txt', 'm1bar_3_txt', 'm1_total'] },
        { ids: ['m2bar_1', 'm2bar_2', 'm2bar_3'], options: { transformOrigin: '50% 100%' } },
        { ids: ['m2bar_1_txt', 'm2bar_2_txt', 'm2bar_3_txt', 'm2_total'] },
        { ids: ['c1', 'c2', 'c3', 'c4'], stagger: 0.1 },
        { ids: ['m4', 'm3'], stagger: 0.1 }
      ]

      animations.forEach((a) => {
        const elems = a.ids.map((i) => doc.getElementById(i))

        timeline.from(
          elems, 2.0,
          Object.assign(options, (a.options || {})),
          (a.stagger || 0.2), (a.position || '-=1.5')
        )
      })

      timeline.restart()
    })
  }

  function loadTotalWaterUsed() {
    const waterObject = document.getElementById('statista-planet-water')
    waterObject.addEventListener('load', () => {
      const doc = waterObject.contentDocument

      TweenLite.set(doc.getElementById('water_used_base'), { visibility: 'visible' })

      const counts = [
        { target: 1963267.48, prefix: 7,dec:2, duration: 6 },
        { target: 4286488.07,prefix: 3,dec:2, duration: 7 },
        { target: 17.86,dec:2, duration: 7 }
      ]

      counts.forEach((count, i) => {
        const elem = doc.getElementById(`m${i+1}_txt_1`)

        TweenLite.to({ val: 0 }, count.duration, {
          val: count.target,
          ease: Power3.easeOut, delay: 0.2,
          onUpdate: function() {
            elem.textContent = `${count.prefix || ''}${formatNumber(this.target.val.toFixed(count.dec))}`
          }
        })
      })

      const timeline = new TimelineMax({ paused: true })

      const options = {
        transformOrigin: '50% 50%',
        alpha: 0,
        y: '+=200',
        ease: Expo.easeInOut
      }

      const elems = Array.from(doc.querySelectorAll('#water_used_base > g[id^="m"]')).reverse()
      timeline.staggerFrom(elems, 2.0, options, 0.2)

      timeline.from(doc.getElementById('center_dot'), 2.0, options, '-=2.6')

      timeline.restart()
    })
  }

  //get IDs for the SVGs
  var planetEnergy = false;
  var getPlanetEnergyID = document.getElementById("svg_planet");

  var planetWater = false;
  var getPlanetWaterID = document.getElementById("svg_planet_water");

  var planetWaste = false;
  var getPlanetWasteID = document.getElementById("svg_planet_waste");

  //check if the section with SVG visible and present on the page
  $(window).scroll(function(){
    if (planetEnergy == false && getPlanetEnergyID !== null){
      if (elementInView($('#svg_planet'))) {
        $('.svg  .svg_desktop_default').css({ 'display':'none' });
        getPlanetEnergyID.innerHTML = '<section><object id="statista-planet-ghg-emissions" type="image/svg+xml" data="/sites/g/files/qbfpbp306/themes/site/ul_com_theme/vendor/SVG_sustainability_report_2020/img/2021_planet_Energy.svg"></object></section>';
        //load SVG animation
        loadGhgEmissions();
        //update variable to load it just once
        planetEnergy = true;
      }
    }
    if (planetWater == false && getPlanetWaterID !== null){
      if (elementInView($('#svg_planet_water'))) {
        $('.svg  .svg_desktop_default').css({ 'display':'none' });
        getPlanetWaterID.innerHTML = '<section><object id="statista-planet-water" type="image/svg+xml" data="/sites/g/files/qbfpbp306/themes/site/ul_com_theme/vendor/SVG_sustainability_report_2020/img/2021_planet_Water.svg"></object></section>';
        //load SVG animation
        loadTotalWaterUsed();
        //update variable to load it just once
        planetWater = true;
      }
    }
    if (planetWaste == false && getPlanetWasteID !== null){
      if (elementInView($('#svg_planet_waste'))) {
        $('.svg  .svg_desktop_default').css({ 'display':'none' });
        getPlanetWasteID.innerHTML = '<section><object id="statista-planet-waste" type="image/svg+xml" data="/sites/g/files/qbfpbp306/themes/site/ul_com_theme/vendor/SVG_sustainability_report_2020/img/2021_planet_Waste.svg"></object></section>';
        //load SVG animation
        loadWaste();
        //update variable to load it just once
        planetWaste = true;
      }
    }
  });

})(jQuery, drupalSettings);
