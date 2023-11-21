var additionalOptions = {
    initial_zoom: 3,
    timenav_height_percentage: 10,
    timenav_mobile_height_percentage: 10
}
// The TL.Timeline constructor takes at least two arguments:
// the id of the Timeline container (no '#'), and
// the URL to your JSON data file or Google spreadsheet.
// the id must refer to an element "above" this code,
// and the element must have CSS styling to give it width and height
// optionally, a third argument with configuration options can be passed.
// See below for more about options.
timeline = new TL.Timeline('timeline-embed', 'https://docs.google.com/spreadsheets/d/1W7TUmvhvu4Z7umozNWV1-7nkcIU7mWU_IawZPfIn9yU/pubhtml', additionalOptions);
