/**
 * 
 * @author 		: Saravana Kumar K
 * @copyright 	: Sarkware Research & Development (OPC) Pvt Ltd
 * 
 * Wcff client controller module
 * 
 */
(function($) {	
	
	var 
		/* Mask object for showing loading spinner */
		mask = null,
		/* Flaq for syncronized Ajax Request Handling */
		ajaxFlaQ = true,
		/* Used to holds the object that is being send to server */
		request = null,
		/* Used to holds the response from the server */
		response = null;
	
	/* JS array compare */
	Array.prototype.equals = function (array) {
	    if (!array)
	        return false; 
	    if (this.length != array.length)
	        return false;
	    for (var i = 0, l=this.length; i < l; i++) {
	        if (this[i] instanceof Array && array[i] instanceof Array) {
	            if (!this[i].equals(array[i]))
	                return false;       
	        }           
	        else if (this[i] != array[i]) { 
	            return false;   
	        }           
	    }       
	    return true;
	}
	Object.defineProperty(Array.prototype, "equals", {enumerable: false});
	
	/**
	 * 
	 * money formater borrowed from : https://stackoverflow.com/questions/149055/how-to-format-numbers-as-currency-string/149099#149099
	 * 
	 */
	Number.prototype.formatMoney = function(decPlaces, thouSeparator, decSeparator) {
	    var n = this,
	        decPlaces = isNaN(decPlaces = Math.abs(decPlaces)) ? 2 : decPlaces,
	        decSeparator = decSeparator == undefined ? "." : decSeparator,
	        thouSeparator = thouSeparator == undefined ? "," : thouSeparator,
	        sign = n < 0 ? "-" : "",
	        i = parseInt(n = Math.abs(+n || 0).toFixed(decPlaces)) + "",
	        j = (j = i.length) > 3 ? j % 3 : 0;
	    return sign + (j ? i.substr(0, j) + thouSeparator : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thouSeparator) + (decPlaces ? decSeparator + Math.abs(n - i).toFixed(decPlaces).slice(2) : "");
	};
	
	/**
	 * 
	 * Wcff Field Ruler Module
	 * 
	 */
	var wcffFieldRuler = function() {
		this.initialize = function() {
			$(document).on("change", "[data-has_field_rules=yes]", this, function(e) {
				e.data.handleFieldChangeEvent($(this));
			});
			$("[data-has_field_rules=yes]").trigger("change");
		};
		
		this.handleFieldChangeEvent = function(_field) {
			var i = 0,
				j = 0,
				me = this,
				days = [],
				date = "",
				value = "",
				fkeys = [],	
				dates = [],
				flaQ = false,
				chosen_date = "",
				common_items = [],
				fkey = _field.attr( "data-fkey"),	
				ftype = _field.attr("data-field-type"),						
				container = _field.closest("div.wcff-fields-group"),
				custom_layout = container.attr("data-custom-layout");
			
			if (ftype == "radio") {
				value = _field.closest("ul").find("input[type=radio]:checked").val();
			} else if (ftype == "checkbox") {
				value = _field.closest("ul").find("input[type=checkbox]:checked").map(function() {
				    return me.escapeQuote(this.value);
				}).get();				
			} else if (ftype == "datepicker") {
				day = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"];
				chosen_date = _field.datepicker("getDate");
			} else if (ftype == "text" || ftype == "number" 
					|| ftype == "select" || ftype == "textarea" 
					|| ftype == "colorpicker") {
				value = _field.val();
			}
			
			if (wcff_fields_rules_meta[fkey]) {	
				for (i = 0; i < wcff_fields_rules_meta[fkey].length; i++) {
					if (wcff_fields_rules_meta[fkey][i].logic == "equal" && wcff_fields_rules_meta[fkey][i].expected_value == value) {
						this.handleFieldsVisibility(container, wcff_fields_rules_meta[fkey][i].field_rules);
					} else if (wcff_fields_rules_meta[fkey][i].logic == "not-equal" && wcff_fields_rules_meta[fkey][i].expected_value != value) {
						this.handleFieldsVisibility(container, wcff_fields_rules_meta[fkey][i].field_rules);
					} else if (wcff_fields_rules_meta[fkey][i].logic == "not-null" && value) {
						this.handleFieldsVisibility(container, wcff_fields_rules_meta[fkey][i].field_rules);
					} else if (wcff_fields_rules_meta[fkey][i].logic == "is-only" && wcff_fields_rules_meta[fkey][i].expected_value.equals(value)) {
						this.handleFieldsVisibility(container, wcff_fields_rules_meta[fkey][i].field_rules);
					} else if (wcff_fields_rules_meta[fkey][i].logic == "is-also" && wcff_fields_rules_meta[fkey][i].expected_value.some(r=> value.includes(r))) {
						this.handleFieldsVisibility(container, wcff_fields_rules_meta[fkey][i].field_rules);
					} else if (wcff_fields_rules_meta[fkey][i].logic == "any-one-of") {
						var common_items = this.fetchCommonItems(wcff_fields_rules_meta[fkey][i].expected_value, value);
						if (common_items.length <= wcff_fields_rules_meta[fkey][i].expected_value.length) {
							flaQ = true;
							for (j = 0; j < common_items.length; j++) {
								if (wcff_fields_rules_meta[fkey][i].expected_value.indexOf(common_items[j]) === -1) {
									flaQ = false;
								}
							}
							if (flaQ) {
								this.handleFieldsVisibility(container, wcff_fields_rules_meta[fkey][i].field_rules);
							}
						}
					} else if (wcff_fields_rules_meta[fkey][i].logic == "days" && Array.isArray(wcff_fields_rules_meta[fkey][i].expected_value)) {
						if (wcff_fields_rules_meta[fkey][i].expected_value.indexOf(chosen_date.getDay()) != -1) {
							this.handleFieldsVisibility(container, wcff_fields_rules_meta[fkey][i].field_rules);
						}
					} else if (wcff_fields_rules_meta[fkey][i].logic == "specific-dates" && wcff_fields_rules_meta[fkey][i].expected_value != "") {
						dates = wcff_fields_rules_meta[fkey][i].expected_value.split(",");
						for (j = 0; j < dates.length; j++) {
							date = dates[j].trim().split("-");
							if ((parseInt(date[0].trim()) == (chosen_date.getMonth()+1)) && (parseInt(date[1].trim()) == chosen_date.getDate()) && (parseInt(date[2].trim()) == chosen_date.getFullYear())) {
								this.handleFieldsVisibility(container, wcff_fields_rules_meta[fkey][i].field_rules);
							}
						}
					} else if (wcff_fields_rules_meta[fkey][i].logic == "weekends-weekdays") {
						if (wcff_fields_rules_meta[fkey][i].expected_value == "weekends") {
							if( chosen_date.getDay() == 6 || chosen_date.getDay() == 0 ) {
								this.handleFieldsVisibility(container, wcff_fields_rules_meta[fkey][i].field_rules);
							}
						} else {
							if( chosen_date.getDay() != 6 || chosen_date.getDay() != 0 ) {
								this.handleFieldsVisibility(container, wcff_fields_rules_meta[fkey][i].field_rules);
							}
						}
					} else if (wcff_fields_rules_meta[fkey][i].logic == "specific-dates-each-month") {
						dates = wcff_fields_rules_meta[fkey][i].expected_value.split(",");
						for (j = 0; j < dates.length; j++) {
							if (parseInt(dates[j].trim()) == chosen_date.getDay()) {
								this.handleFieldsVisibility(container, wcff_fields_rules_meta[fkey][i].field_rules);
							}
						}
					}					
				}					
			}
		}
			
		this.handleFieldsVisibility = function(_container, _rules, ) {
			var parent = null,
				layout = _container.attr("data-custom-layout");
			_container.find(".wccpf-field ").each(function () {
				parent = (layout == "yes") ? $(this).closest("div.wcff-layout-form-col") : $(this).closest("table.wccpf_fields_table");
				if (_rules[$(this).attr("data-fkey")] && _rules[$(this).attr("data-fkey")] != "Nill") {
					if (_rules[$(this).attr("data-fkey")] == "show") {
						parent.show();
					} else {
						parent.hide();
					}
				}
			});
		};
		
		this.fetchCommonItems = function(_a1, _a2) {
			return $.grep(_a1, function(element) {
			    return $.inArray(element, _a2 ) !== -1;
			});
		};
		
		this.escapeQuote = function(_str) {	
			if (_str) {
				_str = _str.replace( /'/g, '&#39;' );
				_str = _str.replace( /"/g, '&#34;' );
			}			
			return _str;
		};
		
		this.unEscapeQuote = function(_str) {
			if (_str) {
				_str = _str.replace( /&#39;/g, "'" );
				_str = _str.replace( /&#34;/g, '"' );
			}
			return _str;
		};
	};
	
	/**
	 * 
	 * Wcff Cloning module
	 * 
	 */
	var wcffCloner = function() {
		this.initialize = function() {
			$(document).on("change", "input[name=quantity]", function() {
				
				var qty = parseInt($(this).val());
				var prev_qty = parseInt($("#wccpf_fields_clone_count").val());
				$("#wccpf_fields_clone_count").val(qty);
				
				if (prev_qty < qty) {					
					var i = 0,
						j = 0,
						x = 0,
						cloned = null,
						group = null,
						groups = null,
						wrapper = null,
						cloneable = false;					
						
					wrapper = $(".wccpf-fields-group-container");
						for (j = 0; j < wrapper.length; j++) {							
							group = $(wrapper[j]).find("> div:not(.cloned)");
							if (group && group.length > 0) {
								if (group.attr("data-group-clonable") == "no") {
									continue;
								}
								cloned = null;
								for (i = prev_qty; i < qty; i++) {
								
								cloned = group.clone(true);
								cloned.addClass("cloned");
								cloned.find("script").remove();				
								cloned.find("div.sp-replacer").remove();
								cloned.find("span.wccpf-fields-group-title-index").html(i + 1);
								cloned.find(".hasDatepicker").attr( "id", "" );
								cloned.find(".hasDatepicker").removeClass( "hasDatepicker" );						
								cloned.find(".wccpf-field").each(function() {
									cloneable = $(this).attr('data-cloneable');
									if ($(this).attr( "wccpf-type" ) === "checkbox" || $(this).attr( "wccpf-type" ) === "radio") {
										cloneable = $(this).closest("ul").attr('data-cloneable');
									}
									/* Check if the field is allowed to clone */
									if (cloneable !== "no") {
										var name_attr = $(this).attr("name");					
										if( name_attr.indexOf("[]") != -1 ) {
											var temp_name = name_attr.substring( 0, name_attr.lastIndexOf("_") );							
											name_attr = temp_name + "_" + (i + 1) + "[]";						
										} else {
											name_attr = name_attr.slice( 0, -1 ) + (i + 1);
										}
										$(this).attr( "name", name_attr );
									} else {
										/* Otherwise remove from cloned */								
										$(this).closest("table.wccpf_fields_table").remove();																
									}			 				
								});
								/* Check for the label field - since label is using different class */
								cloned.find(".wcff-label").each(function() {
									cloneable = $(this).attr('data-cloneable');	
									var label_name_attr = $(this).find("input").attr( "name" ).slice( 0, -1 ) + i;
									$(this).find("input").attr( "name", label_name_attr );
									if (typeof cloneable === typeof undefined || cloneable === false) {
										$(this).remove();
									}
								});
								
								/* Remove empty columns and rows */
								cloned.find("div[class=wcff-layout-form-col]:not(:has(*))").remove();
								cloned.find("div[class=wcff-layout-form-row]:not(:has(*))").remove();								
								
								$(wrapper[j]).append(cloned);
								/* Trigger the color picker init function */
								setTimeout( function(){ 
									init_color_pickers();
									//group.find( '[data-has_field_rules="yes"]' ).trigger( "change" );
								}, 500 );							
							}
						}							
					}
					
				} else {					
					//$("div.wccpf-fields-group:eq("+ ( product_count - 1 ) +")").nextAll().remove();	
					var diff = prev_qty - qty;
					wrapper = $(".wccpf-fields-group-container");
					for (j = 0; j < wrapper.length; j++) {
						groups = $(wrapper[j]).find("> div"); console.log("Original Count : "+ groups.length);
						for (x = 0; x < diff; x++) {
							wrapper.find("> div:nth-child(" + (prev_qty - x) + ")").remove();
						}											
					}					
				}				
			});
			/* Trigger to change event - fix for min product quantity */
			setTimeout(function(){ $("input[name=quantity]").trigger("change"); }, 300);
		};
	};
	
	/**
	 * 
	 * Wcff validation module
	 * 
	 */
	var wcffValidator = function() {		
		this.isValid = true;		
		this.initialize = function() {						
			$(document).on("submit", "form.cart", this, function(e) {
				var me = e.data; 
				e.data.isValid = true;				
				$(".wccpf-field").each(function() {
					if ($(this).attr("wccpf-mandatory") === "yes") {
						me.doValidate($(this));
					}					
				});					
				return e.data.isValid;
			});
			if (wccpf_opt.validation_type === "blur") {
				$( document ).on( "blur", ".wccpf-field", this, function(e) {	
					if ($(this).attr("wccpf-mandatory") === "yes") {
						e.data.doValidate($(this));
					}
				});
			}
		};
		
		this.doValidate = function(field) {			
			if (field.attr("wccpf-type") !== "radio" && field.attr("wccpf-type") !== "checkbox" && field.attr("wccpf-type") !== "file") {
				if (field.attr("wccpf-type") !== "select") {
					if (this.doPatterns(field.attr("wccpf-pattern"), field.val())) {						
						field.nextAll(".wccpf-validation-message").hide();
					} else {						
						this.isValid = false;
						field.nextAll(".wccpf-validation-message").show();
					}
				} else {
					if (field.val() !== "" && field.val() !== "wccpf_none") {
						field.nextAll(".wccpf-validation-message").hide();
					} else {
						this.isValid = false;
						field.nextAll(".wccpf-validation-message").show();
					}
				}							
			} else if (field.attr("wccpf-type") === "radio") {				
				if (field.closest("ul").find("input[type=radio]").is(':checked')) {
					field.closest("ul").next().hide();
				} else {
					field.closest("ul").next().show();
					this.isValid = false;					
				}	 			
			} else if (field.attr("wccpf-type") === "checkbox") {			
				var values = field.closest("ul").find("input[type=checkbox]:checked").map(function() {
				    return this.value;
				}).get();
				if (values.length === 0) {
					field.closest("ul").next().show();
					this.isValid = false;
				} else {						
					field.closest("ul").next().hide();
				}			
			} else if (field.attr("wccpf-type") === "file") {		
				if (field.val() == "") {
					field.next().show();
					this.isValid = false;
				} else {
					field.next().hide();
				}									
			}
		}
		
		this.doPatterns = function(patt, val) {
			var pattern = {
				mandatory	: /\S/, 
				number		: /^-?\d+\.?\d*$/,
				email		: /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i,	      	
			};			    
		    return pattern[patt].test(val);	
		};
		
	};
	
	/* Masking object ( used to mask any container whichever being refreshed ) */
	var wcffMask = function() {
		this.top = 0;
		this.left = 0;
		this.bottom = 0;
		this.right = 0;
		
		this.target = null;
		this.mask = null;
		
		this.getPosition = function( target ) {
			this.target = target;		
			
			var position = this.target.position();
			var offset = this.target.offset();
		
			this.top = offset.top;
			this.left = offset.left;
			this.bottom = $(window).width() - position.left - this.target.width();
			this.right = $(window).height() - position.right - this.target.height();
		};

		this.doMask = function(_target) {
			if (_target) {
				this.target = _target;			
				this.mask = $('<div class="wcff-dock-loader"></div>');						
				this.target.append(this.mask);
				this.mask.css("left", "0px");
				this.mask.css("top", "0px");
				this.mask.css("right", this.target.innerWidth()+"px");
				this.mask.css("bottom", this.target.innerHeight()+"px");
				this.mask.css("width", this.target.innerWidth()+"px");
				this.mask.css("height", this.target.innerHeight()+"px");
			}			
		};

		this.doUnMask = function() {
			if (this.mask) {
				this.mask.remove();
			}			
		};
	};
	
	var wcff_pricing_handler = function() {
		
		this.init = function() {
			this.registerEvents();
		};
		
		this.registerEvents = function() {
			
		};
		
		this.calculatePrice = function() {
			
		};
		
	};
	
	function init_color_pickers() {
		var i = 0,
			j = 0,
			config = {},
			palette = [],
			keys = Object.keys(wcff_color_picker_meta);
		for (i = 0; i < keys.length; i++) {	
			config = {}
			config["color"] = wcff_color_picker_meta[keys[i]]["default_value"];
			config["preferredFormat"] = wcff_color_picker_meta[keys[i]]["color_format"];			
			if (wcff_color_picker_meta[keys[i]]["palettes"] && wcff_color_picker_meta[keys[i]]["palettes"].length > 0) {				
				config["showPalette"] = true;
				if (wcff_color_picker_meta[keys[i]]["show_palette_only"] == "yes") {
					config["showPaletteOnly"] = true;
				}
				
				for (j = 0; j < wcff_color_picker_meta[keys[i]]["palettes"].length; j++) {
					palette.push(wcff_color_picker_meta[keys[i]]["palettes"][j].split(','));
				}
				config["palette"] = palette;
			}			

			if( wcff_color_picker_meta[keys[i]]["show_palette_only"] != "yes" && wcff_color_picker_meta[keys[i]]["color_text_field"] == "yes") {
				config["showInput"] = true;
			}

			$("input.wccpf-color-"+ keys[i]).spectrum(config);
		}
	}
	
	function renderVariationFields() {
		var i = 0,
			keys = [];
		/* Hide the spinner */
		$("div.wccvf-loading-spinner").remove();
		/* Enable the variation selects */
		$("table.variations select").prop("disabled", false);
		/* Inject widget */
		$("div.wcff-variation-fields").html(response.payload.html);
		
		/* Parse the meta */
		response.payload.meta = JSON.parse(response.payload.meta);
		
		/* Inject meta */
		if (wcff_date_picker_meta) {
			wcff_date_picker_meta = Array.isArray(wcff_date_picker_meta) ? {} : wcff_date_picker_meta;
			keys = Object.keys(response.payload.meta.date_picker_meta);
			for (i = 0; i < keys.length; i++) {
				wcff_date_picker_meta[keys[i]] = response.payload.meta.date_picker_meta[keys[i]];
			}
		}
		if (wcff_color_picker_meta) {
			wcff_color_picker_meta = Array.isArray(wcff_color_picker_meta) ? {} : wcff_date_picker_meta;
			keys = Object.keys(response.payload.meta.color_picker_meta);
			for (i = 0; i < keys.length; i++) {
				wcff_color_picker_meta[keys[i]] = response.payload.meta.color_picker_meta[keys[i]];
			}
			/* Refresh the color picker widgets */
			init_color_pickers();
		}		
		if (wcff_fields_rules_meta) {
			wcff_fields_rules_meta = Array.isArray(wcff_fields_rules_meta) ? {} : wcff_date_picker_meta;
			keys = Object.keys(response.payload.meta.fields_rules_meta);
			for (i = 0; i < keys.length; i++) {
				wcff_fields_rules_meta[keys[i]] = response.payload.meta.fields_rules_meta[keys[i]];
			}
		}
		if (wcff_pricing_rules_meta) {
			wcff_pricing_rules_meta = Array.isArray(wcff_pricing_rules_meta) ? {} : wcff_date_picker_meta;
			keys = Object.keys(response.payload.meta.pricing_rules_meta);
			for (i = 0; i < keys.length; i++) {
				wcff_pricing_rules_meta[keys[i]] = response.payload.meta.pricing_rules_meta[keys[i]];				
			}
		}
		
		setTimeout(function() {
			update_negotiated_price();
		}, 200);	
	}
	
	function wcffPricingHanlder(_payload) {
		
		var i, 
			j,
			rule,
			childs,
			keys = [],
			pcontainer,
			variations = [],
			base_price = 0,
			additonal_cost = 0,
			replace_amount = 0,
			fields = _payload._fields_data;
		
		if (wcff_is_variable == "yes") {
			if ($("input[name=variation_id]").val() != "") {
				variations = $("form.variations_form").attr("data-product_variations");
				variations = JSON.parse(variations);
				keys = Object.keys(variations);
				for (i = 0; i < keys.length; i++) {
					if (variations[keys[i]]["variation_id"] == $("input[name=variation_id]").val()) {
						base_price = variations[keys[i]]["display_regular_price"];
					}
				}
			} else {
				/* Nothing to do */
				return;
			}
		} else {
			base_price = wcff_product_price;
		}
		
		for (i = 0; i < fields.length; i++) {		
			if (wcff_pricing_rules_meta && wcff_pricing_rules_meta[fields[i]["fkey"]]) {
				rules = wcff_pricing_rules_meta[fields[i]["fkey"]];
				for (j = 0; j < rules.length; j++) {
					
					if (wcffCheckPricingRules(rules[j], fields[i]["fval"], fields[i]["ftype"], fields[i]["dformat"])) {			
						
						if (rules[j]["tprice"] == "cost") {					
							/* Cost mode */
							if (rules[j]["ptype"] == "add") {
								additonal_cost += parseFloat(rules[j]["amount"]);
							} else if (rules[j]["ptype"] == "sub") {
								additonal_cost -= parseFloat(rules[j]["amount"]);
							} else {
								/* Replace */
								replace_amount += parseFloat(rules[j]["amount"]);
							}						
						} else {						
							/* Percent mode */
							if (rules[j]["ptype"] == "add") {							
								additonal_cost += ((parseFloat(rules[j]["amount"]) / 100) * base_price);
							} else if (rules[j]["ptype"] == "sub") {
								additonal_cost -= ((parseFloat(rules[j]["amount"]) / 100) * base_price);
							} else {
								/* Replace */
								replace_amount += ((parseFloat(rules[j]["amount"]) / 100) * base_price);
							}						
						}
						
					}
				}
			}
		}	
		
		if (replace_amount > 0) {
			base_price = additonal_cost + replace_amount;		 
		} else {
			base_price = base_price + additonal_cost + replace_amount;		
		}
						
		pcontainer = (wcff_is_variable == "yes") ? $("form.variations_form span.amount") : $("div.summary span.amount");		
		childs = pcontainer.children();
		pcontainer.text((base_price).formatMoney()).prepend(childs);	
	}
	
	function wcffCheckPricingRules(_rule, _value, _ftype, _dformat) {
		var i, 
			day,
			sdate,
			sdates;	
		
		if ((_rule && _rule["expected_value"] && _rule["logic"] && _value != "") || _ftype == "datepicker") {
			if (_ftype != "checkbox" && _ftype != "datepicker") {
                if (_rule["logic"] == "equal") {
                    return (_rule["expected_value"] == _value);
                } else if (_rule["logic"] == "not-equal") {
                    return (_rule["expected_value"] != _value);
                } else if (_rule["logic"] == "greater-than") {
                    return (parseFloat(_value) > parseFloat(_rule["expected_value"]));
                } else if (_rule["logic"] == "less-than") {
                    return (parseFloat(_value) < parseFloat(_rule["expected_value"]));
                } else if (_rule["logic"] == "greater-than-equal") {
                    return (parseFloat(_value) >= parseFloat(_rule["expected_value"]));
                } else if (_rule["logic"] == "less-than-equal") {
                    return (parseFloat(_value) <= parseFloat(_rule["expected_value"]));
                } else if (_rule["logic"] == "not-null" ) {                    
                    if (_value.trim() != ""){
                	    return true;
                	} else {
                	    return false;
                	}
                }
            } else if (_ftype == "checkbox") {
                /* This must be a check box field */
                if (Array.isArray(_rule["expected_value"]) && Array.isArray(_value)) {
                    if (_rule["logic"] == "is-only") { 
                        /* User chosen option (or options) has to be exact match */
                        /* In that case both end has to be same quantity */
                        if (_rule["expected_value"].length == _value.length) {
                            /* Now check for the individual options are equals */
                        	for (i = 0; i < _rule["expected_value"].length; i++) {
                        		if (_value.indexOf(_rule["expected_value"][i]) == -1) {
                        			/* Well has exact quantity on both side but with one or more different values */
                        			return false;
                        		}
                        	}                        	
                            /* Has equal options, and all are matching with expected values */
                            return true;
                        }
                    } else if (_rule["logic"] == "is-also") {
                        /* User chosen option should contains expected option
                         * There can be other options also chosen (but expected option has to be one of them) */
                        if (_value.length >= _rule["expected_value"].length) {
                        	for (i = 0; i < _rule["expected_value"].length; i++) {
                        		if (_value.indexOf(_rule["expected_value"][i]) == -1) {                        			
                        			return false;
                        		}
                        	}                            
                            /* Well expected option(s) is chosen by the User */
                            return true;
                        }
                    } else if (_rule["logic"] == "any-one-of") {
                        /* Well there can be more then one expected options, but any one of them are present 
                         * with the user submitted options then rules are met */                        
                        for (i = 0; i < _rule["expected_value"].length; i++) {
                        	if (_value.indexOf(_rule["expected_value"][i]) != -1) {                        			
                        		return true;
                    		}
                        }                        
                    }
                }
            } else if (_ftype == "datepicker") {            	
            	const user_date = moment(_value, _dformat);            	  
                if (user_date && _rule["expected_value"]["dtype"] && _rule["expected_value"]["value"]) { 
                    if (_rule["expected_value"]["dtype"] == "days") {
                        /* If user chosed any specific day like "sunday", "monday" ... */
                    	day = user_date.format("dddd");                    	
                    	if (Array.isArray(_rule["expected_value"]["value"]) && _rule["expected_value"]["value"].indexOf(day.toLowerCase()) != -1) {
                    		return true;
                    	}                    	
                    } 
                    if (_rule["expected_value"]["dtype"] == "specific-dates") {             
                        /* Logic for any specific date matches ( Exact date ) */
                    	sdates = _rule["expected_value"]["value"].split(",");                        
                    	if (Array.isArray(sdates)) {
                    		for (i = 0; i < sdates.length; i++) {
                    			sdate = moment(sdates[i].trim(), "M-D-YYYY");
                    			if (user_date.format("M-D-YYYY") == sdate.format("M-D-YYYY")) {
                    				return true;
                    			}
                    		}
                    	}                    	                        
                    } 
                    if (_rule["expected_value"]["dtype"] == "weekends-weekdays") {
                    	/* Logic for the weekends */
                    	if (_rule["expected_value"]["value"] == "weekends") {
                    		if (user_date.format("dddd").toLowerCase() == "saturday" || user_date.format("dddd").toLowerCase() == "sunday") {
                    			return true;
                    		}
                    	} else {
                    		if (user_date.format("dddd").toLowerCase() != "saturday" && user_date.format("dddd").toLowerCase() != "sunday") {
                    			return true;
                    		}
                    	}
                    }                    
                    if (_rule["expected_value"]["dtype"] == "specific-dates-each-month") {
                    	sdates = _rule["expected_value"]["value"].split(",");
                    	for (i = 0; i < sdates.length; i++) {
                    		if (sdates.trim() == user_date.format("D")) {
                    			return true;
                    		}
                    	}
                    }                    
                }
            }		
		} else { console.log("Invalid rule object") }
		return false;
	}
	
	function update_negotiated_price(_target) {
		
		var i = 0,
			fkey = "",
			fval = "",
			fields = [],
			prod_id = 0,
			payload = {},
			currentField = null,
			variation_not_null = null,
			is_field_cloneable = "no",			
			is_globe_cloneable = wccpf_opt.cloning == "yes" ? "yes" : "no";
		
		if (wccpf_opt["is_page"] == "archive") {
			fields = _target.closest("li.product").find("[data-has_pricing_rules=yes]");
			prod_id = _target.closest("li.product").find("a.add_to_cart_button").attr("data-product_id");
		} else {
			fields = $("[data-has_pricing_rules=yes]");
			prod_id = $("input[name=add-to-cart]").length != 0 ? $("input[name=add-to-cart]").val() : $("button[name=add-to-cart]").val();
		}
		
		if (prod_id) {
			payload = {"_product_id": prod_id, "_variation_id": $("input[name=variation_id]").val(), "_fields_data" : []};
			for (i = 0; i < fields.length; i++ ) {
				currentField = $(fields[i]);
				if (currentField.is(":visible") 
						|| (currentField.is(".wccpf-color") 
						&& currentField.closest("table").is(":visible") 
						&& !currentField.closest("table").is(".wcff_is_hidden_from_field_rule"))) {
					
					fkey = currentField.is("[type=checkbox]") ? currentField.attr("name").replace("[", "").replace("]", "") : currentField.attr("name");
					fval = currentField.is("[type=checkbox]") ? currentField.prop("checked") ? [currentField.val()] : [] : currentField.is("[type=radio]") ? currentField.is(":checked") ? currentField.val() : "" : currentField.val();;
					is_field_cloneable = is_globe_cloneable == "yes" ? currentField.is("[type=radio]") || currentField.is("[type=checkbox]") ? currentField.closest("ul").data( "cloneable" ) : currentField.data( "cloneable" ) : is_globe_cloneable;
					payload._fields_data.push({"is_clonable": is_field_cloneable, "fkey": fkey, "fval": fval, "ftype": currentField.attr("data-field-type"), "dformat": currentField.attr("data-date-format")});
				}
			}			
			wcffPricingHanlder(payload);			
		}
		
	}
	
	/* Request object for all the wcff cart related Ajax operation */
	function prepareRequest(_request, _method, _data, _post) {
		request = {
			method	 	: _method,
			context 	: _request,
			post 		: _post,
			post_type 	: "wccpf",
			payload 	: _data,
		};
	}
	
	/* Ajax response wrapper object */
	function prepareResponse(_status, _msg, _data) {
		response = {
			status : _status,
			message : _msg,
			payload : _data
		};
	}
	
	function dock(_action, _target, is_file) {		
		/* see the ajax handler is free */
		if (!ajaxFlaQ) {
			return;
		}
		$.ajax({  
			type       : "POST",  
			data       : { action : "wcff_ajax", wcff_param : JSON.stringify(request) },  
			dataType   : "json",  
			url        : woocommerce_params.ajax_url,  
			beforeSend : function(){  				
				/* enable the ajax lock - actually it disable the dock */
				ajaxFlaQ = false;	
				/* If target is there, then mask it */
				if (_target) {
					mask.doMask(_target);
				}
			},  
			success    : function(data) {				
				/* disable the ajax lock */
				ajaxFlaQ = true;				
				prepareResponse(data.status, data.message, data.data);		               

				/* handle the response and route to appropriate target */
				if (response.status) {
					responseHandler(_action, _target);
				} else {
					/* alert the user that some thing went wrong */
					//me.responseHandler( _action, _target );
				}				
			},  
			error      : function(jqXHR, textStatus, errorThrown) {                
				/* disable the ajax lock */
				ajaxFlaQ = true;
			},
			complete   : function() {
				//mask.doUnMask();
			}   
		});		
	}
	
	function responseHandler(_action, _target) {
		
		if (!response.status) {
			/* Something went wrong - Do nothing */
			return;
		}
		
		if (_action === "wcff_variation_fields") {
			renderVariationFields();
		}
		
	}	
			
	/**
	 * 
	 * Change event handler for fields which have pricing rules
	 * 
	 */
	$(document).on("change", "[data-has_pricing_rules=yes]", function(e) {
		update_negotiated_price($(this));
	});
	
	/**
	 * 
	 * Datepicker init handler
	 * 
	 */
	$(document).on("focus", "input.wccpf-datepicker", function() {
		/* Fields key used to get the meta */
		var m, d, y,
			config = {},
			meta = null,
			hours = [],
			minutes = [],
			hour_min = [],
			weekenddate = null,
			currentdate = null,
			disableDates = "",
			allowed_dates = "",			
			fkey = $(this).attr("data-fkey");
		/* Make sure the datepicker has meta */
		if (wcff_date_picker_meta && wcff_date_picker_meta[fkey]) {
			meta = wcff_date_picker_meta[fkey];
			
			/* Set localize option */
			if (typeof $ != "undefined" && typeof $.datepicker != "undefined") {
				if (meta["localize"] != "none" && meta["localize"] != "en") {
					$.datepicker.setDefaults($.extend({}, $.datepicker.regional[meta["localize"]]));
				} else {
					$.datepicker.setDefaults($.extend({}, $.datepicker.regional["en-GB"]));
				}
			}
			
			/* Check for timepicker */
			if (meta["field"]["timepicker"] && meta["field"]["timepicker"] === "yes") {
				/* Time picker related config */
				config["controlType"] = "select";
				config["oneLine"] = true;
				config["timeFormat"] = "hh:mm tt";				
				/* Min Max hours and Minutes */
				if (meta["field"]["min_max_hours_minutes"] && meta["field"]["min_max_hours_minutes"] !== "") {
					hour_min = meta["field"]["min_max_hours_minutes"].split("|");
					if (hour_min.length === 2) {
						if (hour_min[0] !== "") {
							hours = hour_min[0].split(":");
							if (hours.length === 2) {
								config["hourMin"] = hours[0];
								config["hourMax"] = hours[1];
							}							
						}
						if (hour_min[1] !== "") {
							minutes = hour_min[1].split(":");
							if (minutes.length === 2) {
								config["minuteMin"] = minutes[0];
								config["minuteMax"] = minutes[1];
							}
						}
					}
				}				
			}
			
			/* Date format */
			config["dateFormat"] = meta["dateFormat"];
			
			if (meta["field"]["display_in_dropdown"] && meta["field"]["display_in_dropdown"] === "yes") {
				config["changeMonth"] = true;
				config["changeYear"] = true;
				config["yearRange"] = meta["year_range"];
			}
			
			if (meta["field"]["disable_date"] && meta["field"]["disable_date"] !== "") {
				if ("future" === meta["field"]["disable_date"]) {
					config["maxDate"] = 0;
				}
				if ("past" === meta["field"]["disable_date"]) {
					config["minDate"] = new Date();
				}
			}
			
			if (meta["field"]["disable_next_x_day"] && meta["field"]["disable_next_x_day"] != ""){
				config["minDate"] = "+'"+ meta["field"]["disable_next_x_day"] +"'d";
			}
			
			if (meta["field"]["allow_next_x_years"] && meta["field"]["allow_next_x_years"] != "" ||
				meta["field"]["allow_next_x_months"] && meta["field"]["allow_next_x_months"] != "" ||
				meta["field"]["allow_next_x_weeks"] && meta["field"]["allow_next_x_weeks"] != "" ||
				meta["field"]["allow_next_x_days"] && meta["field"]["allow_next_x_days"] != "") {
				
				allowed_dates = "";
				if (meta["field"]["allow_next_x_years"] && meta["field"]["allow_next_x_years"] != "") {
					allowed_dates += "+"+ meta["field"]["allow_next_x_years"].trim() +"y ";
				}
				if (meta["field"]["allow_next_x_months"] && meta["field"]["allow_next_x_months"] != "") {
					allowed_dates += "+"+ meta["field"]["allow_next_x_months"].trim() +"m ";
				}
				if (meta["field"]["allow_next_x_weeks"] && meta["field"]["allow_next_x_weeks"] != "") {
					allowed_dates += "+"+ meta["field"]["allow_next_x_weeks"].trim() +"w ";
				}
				if (meta["field"]["allow_next_x_days"] && meta["field"]["allow_next_x_days"] != "") {
					allowed_dates += "+"+ meta["field"]["allow_next_x_days"].trim() +"d";
				}
				config["minDate"] = 0;
				config["maxDate"] = allowed_dates.trim();				
			}
			
			config["onSelect"] = function(dateText) {	
				$(this).trigger("change");						
			    $(this).next().hide();
			};
			
			config["beforeShowDay"] = function(date) {
				var i = 0,
					test = "",
					day = date.getDay(),
					disableDays = "",
					disableDateAll = "";
				
				if (meta["field"]["disable_days"] && meta["field"]["disable_days"].length > 0) {				
						day = date.getDay(),
						disableDays = meta["field"]["disable_days"];
					for (i = 0; i < disableDays.length; i++) {
						test = disableDays[i]
					 	test = test == "sunday" ? 0 : test == "monday" ? 1 : test == "tuesday" ? 2 : test == "wednesday" ? 3 : test == "thursday" ? 4 : test == "friday" ? 5 : test == "saturday" ? 6 : "";
				        if (day == test) {									        
				            return [false];
				        }
					}						
				}
				
				if (meta["field"]["specific_date_all_months"] && meta["field"]["specific_date_all_months"] != "") {			 		
			 			disableDateAll = meta["field"]["specific_date_all_months"].split(",");			 			
			 		for (var i = 0; i < disableDateAll.length; i++) {
						if (parseInt(disableDateAll[i].trim()) == date.getDate()){
							return [false];
						}					
			 		}
				}
				
				if (meta["field"]["specific_dates"] && meta["field"]["specific_dates"] != "") {
					disableDates = meta["field"]["specific_dates"].split(",");
					/* Sanitize the dates */
					for (var i = 0; i < disableDates.length; i++) {	
						disableDates[i] = disableDates[i].trim();
					}		
					/* Form the date string to compare */							
					m = date.getMonth();
					d = date.getDate();
					y = date.getFullYear();
					currentdate = ( m + 1 ) + '-' + d + '-' + y ;
					/* Make dicision */								
					if ($.inArray(currentdate, disableDates) != -1) {
						return [false];
					}				
				}	
				
				if (meta["field"]["disable_next_x_day"] && meta["field"]["disable_next_x_day"] != "") {}
				
				if (meta["field"]["weekend_weekdays"] && meta["field"]["display_in_dropdown"] != "") {
					if (meta["field"]["weekend_weekdays"] == "weekdays"){
						//weekdays disable callback
						weekenddate = $.datepicker.noWeekends(date);
						return [!weekenddate[0]];
					} else if (meta["field"]["weekend_weekdays"] == "weekends") {
						//weekend disable callback						
						return $.datepicker.noWeekends(date);
					}
				}	
				
				return [true];
			};
		}
		
		if (meta["field"]["timepicker"] && meta["field"]["timepicker"] === "yes") {
			$(this).datetimepicker(config);
		} else {
			$(this).datepicker(config);
		}
	});
	
	/**
	 * 
	 * Variation change handler
	 * 
	 */
	$(document).on("change", "input[name=variation_id]", function() {
		var variation_id = $("input[name=variation_id]").val();
		if( variation_id.trim() != "" ) {	
			/* Disable the variation selects */
			$("table.variations select").prop("disabled", true);
			$("a.reset_variations").after($('<div class="wccvf-loading-spinner"></div>'));
			prepareRequest("wcff_variation_fields", "GET", {"variation_id" : variation_id}, "");
			dock("wcff_variation_fields", $("div.wcff-variation-fields"));					
		} else {
			$(".wcff-variation-field").html("");			
		}
	});
	
	/**
	 * 
	 * Last minit cleanup operations, before the Product Form submitted for Add to Cart
	 * 
	 */
	$(document).on( "submit", "form.cart", function() {				
		if (typeof(wccpf_opt.location) !== "undefined") {			
			var me = $(this);		
			$(".wccpf_fields_table").each(function() {
				if ($(this).closest("form.cart").length == 0) {
					var cloned = $(this).clone(true);
					cloned.css("display", "none");
					
					/* Since selected flaq doesn't carry over by Clone method, we have to do it manually */
					/* carry all field value to server */
					if ($(this).find(".wccpf-field").attr("wccpf-type") === "select") {
						cloned.find("select.wccpf-field").val($(this).find("select.wccpf-field").val());
					}
					me.append(cloned);
				}
			});			
		}
		// To remove hidden field table
		$(".wcff_is_hidden_from_field_rule").remove();
	});
	
	$(document).ready(function() {
		
		/* initiate mask object */
		mask = new wcffMask();
		
		/* Initialize color picker fields */
		if (wcff_color_picker_meta && !$.isEmptyObject(wcff_color_picker_meta)) {
			init_color_pickers();
		}
		
		if (typeof wccpf_opt != "undefined") {
			/* Initialize fields cloner module */
			if (typeof(wccpf_opt.cloning) !== "undefined" && wccpf_opt.cloning === "yes") {
				var wcff_cloner_obj = new wcffCloner();
				wcff_cloner_obj.initialize();
			}		
			/* Initialize validation module */
			if (typeof(wccpf_opt.validation) !== "undefined" && wccpf_opt.validation === "yes") {			
				var wcff_validator_obj = new wcffValidator();
				wcff_validator_obj.initialize();
			}
		}
		
		/* Initialize fields ruler module */
		var wcff_ruler_obj = new wcffFieldRuler();
		wcff_ruler_obj.initialize();		
		
		// on load pring negotiation
		setTimeout(function() {
			$('[data-has_field_rules="yes"]').trigger("change");
			if (wccpf_opt["is_page"] != "archive") {
				update_negotiated_price();
			}
		}, 200);
		
	});
	
	
	
})(jQuery);