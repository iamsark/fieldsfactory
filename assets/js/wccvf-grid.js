/**
 * @author  	: Saravana Kumar K
 * @author url 	: http://iamsark.com
 * @url			: http://sarkware.com/
 * @copyrights	: Sarkware Research & Development (OPC) Pvt Ltd
 * @purpose 	: Data Grid Module for Variation mapping list.
 */
/**/
var wccvf_grid = function($, _container) {
	
	/**
	 *
	 * Container element where this grid instance will render the actual data grid 
	 * 
	 */
	this.container = _container;
	
	/**
	 * 
	 * Holds the reference object of grid's header table element 
	 * 
	 */
	this.gridHeader = null;
	
	/**
	 *
	 * Holds the reference object of actual grid table element 
	 * 
	 */
	this.gridTable = null;
	
	/**
	 * 
	 * 
	 * 
	 */
	this.bucket = null;
	
	/**
	 * 
	 * 
	 * 
	 */
	this.products = null;
	
	/**
	 * 
	 * Holds the grid records ( the data, which is about to be rendered ) 
	 * 
	 */
	this.records = null;
	
	/**
	 * 
	 * Holds the total number of pages loaded by the scroll events ( always <= this.totalPages ) 
	 * 
	 */
	this.currentPage = 1;
	
	/**
	 * 
	 * Total number of pages count - used for Lazy Loading Mechanism 
	 *
	 */
	this.totalPages = 0;
	
	/**
	 * 
	 * Record per page - used for Lazy Loading Mechanism 
	 * 
	 */
	this.recordsPerPage = 20;
	
	/**
	 * 
	 * Flag for grid reloading event
	 * 
	 */
	this.isReloading = false;
	
	/**
	 * 
	 * 
	 * 
	 */
	this.targetRow = null;
	
	/**
	 * 
	 * 
	 * 
	 */
	this.currentVariant = null;
	
	
	/**
	 * 
	 * 
	 * 
	 */
	this.init = function() {
		if (wcffObj) {
			/* Register events */
			this.registerEvents();
			/* Render the skeleton */
			this.renderSkeleton();
			/* Fetch the records */
			this.loadRecords();
		} else {
			console.log("wcff admin object not found.!");
		}		
	};
	
	/**
	 * 
	 * 
	 * 
	 */
	this.registerEvents = function() {	
		
	};
	
	/**
	 * 
	 * Renders the skeleton of the Data Grid
	 * 
	 */	
	this.renderSkeleton = function() {
		
		var i = 0, 
			html = '',
			dataGrid = $('<div class="wccvf-data-grid-container"></div>');
		
		/* Clear the parent container */
		this.container.html("");
		
		/** Header block start */
		
		/* Start of header wrapper */
		html = '<div class="wccvf-data-grid-header">';
		
		html = '<div class="wccvf-data-grid-header-table">';
		/* Start of header row */
		html += '<div class="wccvf-data-grid-row">';		
		
		html += '<div class="wccvf-data-grid-cell">Product <input type="text" id="wccvf-grid-search-map-txt" placeholder="Search Mapping ..."/> <img src="'+ wcff_var.asset_url +'/img/search-icon.png" /></div>';
		html += '<div class="wccvf-data-grid-cell">Mappings</div>';
		
		/* End of header row */
		html += '</div>';
		/* End of headedr wrapper */
		html += '</div>';
		html += '</div>';
		
		/* Store the reference to the gridHeader property */
		this.gridHeader = $(html);
		
		/** Header block end */
		
		/** Content block start */
		
		this.gridTable = $('<div class="wccvf-data-grid-content"></div>');
		
		/** Content block end */
		
		/* Combine both header & content */
		dataGrid.append(this.gridHeader);
		dataGrid.append(this.gridTable);
		
		/* Inject the data grid skeleton inside the parent container */
		this.container.append(dataGrid);
		
	};
	
	this.handleSearch = function(_txt) {
		var i = 0,
			keys = [],
			result = {},
			search = _txt.val();
		if (search != "") {			
			keys = Object.keys(this.bucket);
			for (i = 0; i < keys.length; i++) { 
				if (this.bucket[keys[i]].product_title.toUpperCase().indexOf(search.toUpperCase()) !== -1) {
					result[keys[i]] = this.bucket[keys[i]];
				}
			}			
		} else {
			result = this.bucket;
		}
		this.prepareRecords(result);
	};
	
	/**
	 * 
	 * Reload the Grid
	 * 
	 */
	this.reRender = function() {
		
	};
	
	/**
	 * 
	 * 
	 * 
	 */
	this.renderPaginator = function() {
		if (this.totalPages > 1) {
			var i = 0,
				html = '<div class="wccvf-pagination-container"><ul>';			
				html += '<li><a href="#" data-page="prev" class="wccvf-grid-page-btn disabled"><<</a></li>';
			for (i = 0; i < this.totalPages; i++) {
				html += '<li><a href="#" data-page="'+ (i + 1) +'" class="wccvf-grid-page-btn '+ (i === 0 ? "current" : "") +'">'+ (i + 1) +'</a></li>';
			}			
			html += '<li><a href="#" data-page="next" class="wccvf-grid-page-btn '+ (this.totalPages === 1 ? "disabled" : "") +'">>></a></li>';
			html += '</ul></div>';
			this.container.append($(html));
		}		
	};
	
	/**
	 * 
	 * 
	 * 
	 */
	this.loadRecords = function() {
		wcffObj.prepareRequest("GET", "variation_fields_mapping_list", {}, null);
		wcffObj.dock();
	};
	
	/**
	 * 
	 * Fetch the records from the Server
	 * 
	 */
	this.handlePageClick = function(_page) {		
		
		/* Reset dsiabled state of both Prev & Next buttons */
		$("div.wccvf-pagination-container ul > li:first").find("a").removeClass("disabled");
		$("div.wccvf-pagination-container ul > li:last").find("a").removeClass("disabled");
		
		if (_page === "next") {
			this.currentPage = this.currentPage + 1;
			if (this.currentPage === this.totalPages) {
				/* Disable the next button as we reached the last page */
				$("div.wccvf-pagination-container ul > li:last").find("a").addClass("disabled");
			}			
		} else if (_page === "prev") {
			this.currentPage = this.currentPage - 1;
			if (this.currentPage === 1) {
				/* Disable the next button as we reached the last page */
				$("div.wccvf-pagination-container ul > li:first").find("a").addClass("disabled");
			}
		} else {
			this.currentPage = parseInt(_page, 10);		
			if (this.currentPage === this.totalPages) {
				/* Disable the next button as we reached the last page */
				$("div.wccvf-pagination-container ul > li:last").find("a").addClass("disabled");
			}
			if (this.currentPage === 1) {
				/* Disable the next button as we reached the last page */
				$("div.wccvf-pagination-container ul > li:first").find("a").addClass("disabled");
			}
		}
		
		/* Highlight the current page btn */
		$("div.wccvf-pagination-container ul > li:nth-child("+ (this.currentPage + 1) +")").find("a").addClass("current");
		
		/* Well render the records */
		this.renderRecords();
	};
	
	/**
	 * 
	 * Reset Data Grid properties & Views
	 * 
	 */
	this.resetGrid = function() {
		
	};
	
	this.prepareRecords = function(_records) {
		/* Safe to remove mask */
		wcffObj.mask.doUnMask();
		/* Reset the current page property */
		this.currentPage = 1;
		/* Store it for later use */
		this.records = _records;
		/* Extract product ids */
		this.products = Object.keys(this.records);
		
		this.totalPages = Math.ceil(this.products.length / this.recordsPerPage); 
		
		/* Render the pagination block */
		this.renderPaginator();
		
		if (this.isReloading) {
			this.isReloading = false;
			/* Now this for reloading the current view */
			if (this.targetRow) {
				this.renderVariations(this.targetRow.find("a.wccvf-grid-map-product-link"), true);
			}			
		} else {
			/* Now start to render the records */
			this.renderRecords();
		}		
	};
	
	/**
	 * 
	 * Renders the records
	 * 
	 */	
	this.renderRecords = function() {		
		var i = 0,
			j = 0,
			html  = '',
			mcount = 0,
			start_index = ((this.currentPage - 1) * this.recordsPerPage);
			end_index = ((start_index + this.recordsPerPage) < this.products.length) ? (start_index + this.recordsPerPage) : this.products.length;
		
		if (this.products.length > 0) {
				
			html += '<div class="wccvf-data-grid-records-table">';
			for (i = start_index; i < end_index; i++) {
				html += '<div class="wccvf-data-grid-row">';
				
				html += '<div class="wccvf-data-grid-cell"><a href="#" data-pid="'+ this.products[i] +'" class="wccvf-grid-map-product-link">'+ this.records[this.products[i]].product_title +'</a></div>';
				
				mcount = Object.keys(this.records[this.products[i]].variations).length;
				html += '<div class="wccvf-data-grid-cell mapping-stats">'+ (mcount + " Variation(s) found") +'</div>';
							
				html += '</div>';
			}
			html += '</div>';
			
		} else {
			html = '<h3>No mapping yet.!</h3>';
		}
		
		this.gridTable.html("");
		this.gridTable.html(html);
	};
	
	this.renderVariations = function(_item, _reload) {
		var i = 0,
			html = '',
			me = this,
			pid = _item.attr("data-pid"),
			row = _item.closest("div.wccvf-data-grid-row");
		
		if (!this.records[pid]) {
			/* This means there is no variation mapping for this product
			 * result of previus mapping delete operation */
			if (row.next().hasClass("mapping-row")) {
				row.next().remove();
			}			
			row.remove();
			return;
		}
		
		var vids = Object.keys(this.records[pid].variations);
		
		if (!row.next().hasClass("mapping-row") || _reload) {
			html = '<div class="wccvf-data-grid-row mapping-row" style="display: '+ (_reload ? 'table-row' : 'none') +';">';
			
			/* Cell where variations will be rendered */
			html += '<div class="wccvf-data-grid-cell end-points"><ul class="wccvf-grid-variation-list">';
			for (i = 0; i < vids.length; i++) {
				html += '<li><a href="#" data-pid="'+ pid +'" data-vid="'+ vids[i] +'" class="wccvf-grid-map-variation-link" title="Click to view the mapping field\'s groups">'+ this.records[pid].variations[vids[i]].variation_title +'</a></li>';
			}	
			html += '</ul></div>';			
			/* Cell where the mapped wccvf groups will be rendered */
			html += '<div class="wccvf-data-grid-cell wccvf-groups"></div>';			
			html += '</div>';
			html = $(html);	
				
			if (row.next().hasClass("mapping-row") && _reload) {
				/* Since this must be for reload, clear the existing row */
				row.next().remove();
				row.after(html);
				/* Update the stats */
				/* Update mapping count */
				stats = Object.keys(this.records[pid].variations).length +' Variation(s) found';
				row.find("div.wccvf-data-grid-cell.mapping-stats").html(stats);				
				/* Re select the current variant */
				if (this.currentVariant) {
					row.next().find("ul.wccvf-grid-variation-list a").each(function() {						
						if (me.currentVariant == $(this).attr("data-vid")) {
							$(this).trigger("click");
							this.currentVariant = null;
							return;
						}
					});
				}			
			} else {
				row.after(html);
				html.fadeIn("normal");
			}		
		} else {
			row.next().fadeOut("normal", function() {
				row.next().remove();
			});			
		}	
	};
	
	this.renderMappedGroups = function(_item) {
		if (!_item.hasClass("selected")) {
			_item.parent().siblings().find("> a").removeClass("selected");
			_item.addClass("selected");
			
			var i = 0,
				html  = '',
				pid = _item.attr("data-pid"),
				vid = _item.attr("data-vid"),
				groups = this.records[pid].variations[vid].groups;
			
			html = '<ul class="wccvf-grid-mapped-group-list" style="display: none;">';
			for (i = 0; i < groups.length; i++) {
				html += '<li><label>'+ groups[i].gtitle +' <a href="#" class="wccvf-grid-group-remove-btn" data-pid="'+ pid +'" data-vid="'+ vid +'" data-gid="'+ groups[i].gid +'">X</a></label></li>';
			}			
			html += '</ul>';
			html = $(html);		
			
			_item.closest("div.wccvf-data-grid-cell").next().html("").append(html);
			html.fadeIn("normal");
			
			/* Update mapping count */
			stats = Object.keys(this.records[pid].variations).length +' Variation(s) with '+ groups.length +' mapping(s) found';
			_item.closest("div.wccvf-data-grid-row").prev().find("div.wccvf-data-grid-cell.mapping-stats").html(stats);
			
		} else {
			_item.removeClass("selected");
		}
	};
	
	this.showEmptyMessage = function() {
		this.gridTable.html('<h1 class="ikea-acm-empty-record-msg"><i class="fa fa-exclamation"></i> No record found</h1>');
	};
	
	this.showErrorMessage = function() {
		this.gridTable.html('<h1 class="ikea-acm-empty-record-msg"><i class="fa fa-exclamation"></i> Error while fetching records</h1>');
	};
		
	this.escapeHtml = function( _string ) {
		var me = this;
		return String(_string).replace(/[&<>"'`=\/]/g, function (s) {
		    return me.entityMap[s];
		});
	};
	
};