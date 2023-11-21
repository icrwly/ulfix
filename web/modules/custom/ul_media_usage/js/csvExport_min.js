(function($){
	$.fn.extend({
		csvExport: function(options) {
			this.defaultOptions = {
				escapeContent: true,
				title: 'Exported_Table.csv',
				beforeStart: function() {},
				onStringReady: function() {}
			};

			const settings = $.extend({}, this.defaultOptions, options);

			//MULTIPLE OBJECTS HANDLER
			return this.each(function() {
				const $this = $(this);
				const real = {
					x: 0,
					y: 0
				};
				// Objects to insert : { ori : {x:0,y:O}, toDo : xxx, done : xxx }
				const toExpand = {
					x: [],
					y: []
				};
				let theString = '';

				function deleteChecker(parent, pos){
					if(parent[pos].toDo == parent[pos].done){
						parent.splice(pos, pos+1);

						return true;
					}

					return false;
				}

				function spanChecker(){
					let colspanHandler = true;

					while(colspanHandler){
						let broken = false,
							directions = ['y', 'x'];

						for(let k = 0; k< directions.length; k++){
							const direction = directions[k];
							const other = direction == 'y' ? 'x' : 'y';

							for(let i = 0; i < toExpand[direction].length; i++){

								// Move on if task done
								if(deleteChecker(toExpand[direction], i) && i > 0){
									i--;
								}

								if(toExpand[direction].length > 0){
									if(real[other] == toExpand[direction][i].ori[other]){
										if(real[direction] == toExpand[direction][i].ori[direction] + toExpand[direction][i].done){
											theString+='"",';
											toExpand[direction][i].done++;
											broken=true;
											real.x++;
											break;
										}
									}
								}
							}
						}

						if(!broken) colspanHandler=false;
					}
				}

				function contentCheckup(data){
          data = data.replace(/<\!--.*?-->/g, "");
					if(settings.escapeContent) return data.replace(/([\\"])/g, '\\$1');

					return data;
				}

				function b64toBlob(b64Data, contentType, sliceSize) {
					contentType = contentType || '';
					sliceSize = sliceSize || 512;

					let byteCharacters = atob(b64Data);
					let byteArrays = [];

					for (let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
						let slice = byteCharacters.slice(offset, offset + sliceSize),
							byteNumbers = new Array(slice.length);

						for (let i = 0; i < slice.length; i++) {
							byteNumbers[i] = slice.charCodeAt(i);
						}

						let byteArray = new Uint8Array(byteNumbers);

						byteArrays.push(byteArray);
					}

					let blob = new Blob(byteArrays, {type: contentType});

					return blob;
				}

				//BEFORESTART CALLBACK
				settings.beforeStart.call(null, $this);
        let $count = 0;
				$('tr', $this).each(function(){

          $count++;
					const currentTR = $(this);

					currentTR.children().each(function(){

						const currentTD = $(this);

						spanChecker();

						/* CURRENT TD HANDLER __START */
						if(currentTD.is('[colspan]')){
							toExpand.x.push({
								ori: {
									x: real.x,
									y: real.y
								},
								toDo: +currentTD.attr('colspan'),
								done: 1
							});
						}

						if(currentTD.is('[rowspan]')){
							toExpand.y.push({
								ori: {
									x: real.x,
									y: real.y
								},
								toDo: +currentTD.attr('rowspan'),
								done: 1
							});
						}

						let currentCellString = '',
							currentCellNodes = currentTD[0].childNodes;

            // Handle the <TD></TD> cell data.
						for(let i = 0; i < currentCellNodes.length; i++){
              // console.log(currentCellNodes[i]);
              let $tdNode = currentCellNodes[i];

              if($tdNode.nodeType === Node.COMMENT_NODE) {
                continue;
              }
              if($tdNode.nodeType === 3 && ($tdNode.nodeValue == "Operations" || $tdNode.nodeValue == "Thumbnail" )) {
                continue;
              }
              if($tdNode.nodeType === 1 && $tdNode.nodeValue == null &&
                ($tdNode.nodeName == "DIV" || $tdNode.tagName == "IMG")) {
                continue;
              }

              let textInnerText = ($tdNode.innerText) ? $tdNode.innerText : "" ;

              let textCon = $tdNode.textContent.replace(/\s/g, ' ').replace(/\n\n/g, "\n ");
              // Remoe the time "- 08:53" from 02/02/2023 - 08:53 Updated column.
              if (textCon.search(/ - \d+:\d+/g)) {
                textCon = textCon.replace(/ - \d+:\d+/g, "");
              }
              if ($count == 1) {
                textCon = textCon.toUpperCase();
              }
              // Remove the "SORT.." from the header of column.
              if ($count == 1 && textInnerText) {
                textInnerText = textInnerText.replace(/\nSORT ASCENDING/g, '').replace(/\nSORT DESCENDING/g, '');
                currentCellString += textInnerText + ' ';
              }
              else {
                currentCellString += (textInnerText || textCon.length ? textCon : '') + ' ';
              }




						}

						currentCellString = contentCheckup(currentCellString).trim();

						theString+='"'+currentCellString+'",';
						real.x++;
						/* CURRENT TD HANDLER __END */
					});

					theString = theString.substring(0, theString.length - 1);
					theString+='\r\n';
					real.x=0;
					real.y++;
				});

				settings.onStringReady.call(null, theString);

				const
					fileData = window.btoa(unescape(encodeURIComponent(theString))),
					title = settings.title;

				// IE Fix
				if(navigator.userAgent.indexOf('MSIE ') > 0 || navigator.userAgent.match(/Trident.*rv\:11\./)){
					const blobObject = b64toBlob(fileData);

					window.navigator.msSaveOrOpenBlob(blobObject, title);
				}else{
					const a = document.createElement('a');
          // Fix the Japanese/Chinese encode problem.
					// a.href = 'data:application/vnd.ms-excel;base64,' + fileData;
          a.href = 'data:text/csv;charset=utf-8,%EF%BB%BF' + encodeURIComponent(theString);
					a.download = title;

					// Append to current DOM to allow click trigger
					a.style.display = 'none';
					document.body.appendChild(a);

					a.click();

					// Cleanup
					a.remove();
				}
			});
		}
	});
}(jQuery));
