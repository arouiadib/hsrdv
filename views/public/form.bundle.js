/******/!function(e){function t(r){if(n[r])return n[r].exports;var s=n[r]={i:r,l:!1,exports:{}};return e[r].call(s.exports,s,s.exports,t),s.l=!0,s.exports}// webpackBootstrap
/******/
var n={};t.m=e,t.c=n,t.i=function(e){return e},t.d=function(e,n,r){t.o(e,n)||Object.defineProperty(e,n,{configurable:!1,enumerable:!0,get:r})},t.n=function(e){var n=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(n,"a",n),n},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="",t(t.s=13)}([function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var r=n(11),s=n.n(r),i=window.$;i(function(){function e(e){e&&e.preventDefault();var n=l.replace(/__name__/g,o.children.length+1),s=i(n);i("#"+r).append(s),t(s)}function t(e){var t=e.closest(".custom_collection"),n=i('<button class="remove_custom_url btn btn-primary mt-1">'+t.data("deleteButtonLabel")+"</button>");n.on("click",function(e){return e.preventDefault(),i(e.target).closest(".row").remove(),!1}),e.find(".locale-input-group").first().closest(".col-sm-12").append(n)}new s.a({localeInputSelector:".js-locale-input"});var n=i(".add-collection-btn");n.on("click",e);var r=n.data().collectionId,o=document.getElementById(r),l=o.dataset.prototype;o.children.length?i(".custom_collection .col-sm-12").each(function(e,n){t(i(n))}):e()})},,,,function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.EventEmitter=void 0;var r=n(12),s=function(e){return e&&e.__esModule?e:{default:e}}(r),i=t.EventEmitter=new s.default;/**
                                                                   * Copyright since 2007 PrestaShop SA and Contributors
                                                                   * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
                                                                   *
                                                                   * NOTICE OF LICENSE
                                                                   *
                                                                   * This source file is subject to the Open Software License (OSL 3.0)
                                                                   * that is bundled with this package in the file LICENSE.md.
                                                                   * It is also available through the world-wide-web at this URL:
                                                                   * https://opensource.org/licenses/OSL-3.0
                                                                   * If you did not receive a copy of the license and are unable to
                                                                   * obtain it through the world-wide-web, please send an email
                                                                   * to license@prestashop.com so we can send you a copy immediately.
                                                                   *
                                                                   * DISCLAIMER
                                                                   *
                                                                   * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
                                                                   * versions in the future. If you wish to customize PrestaShop for your
                                                                   * needs please refer to https://devdocs.prestashop.com/ for more information.
                                                                   *
                                                                   * @author    PrestaShop SA and Contributors <contact@prestashop.com>
                                                                   * @copyright Since 2007 PrestaShop SA and Contributors
                                                                   * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
                                                                   */
t.default=i},,,,,,,function(e,t,n){"use strict";function r(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}Object.defineProperty(t,"__esModule",{value:!0});var s=function(){function e(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}return function(t,n,r){return n&&e(t.prototype,n),r&&e(t,r),t}}(),i=n(4),o=window,l=o.$,c=function(){function e(t){var n=this;r(this,e);var s=t||{};return this.localeItemSelector=s.localeItemSelector||".js-locale-item",this.localeButtonSelector=s.localeButtonSelector||".js-locale-btn",this.localeInputSelector=s.localeInputSelector||".js-locale-input",this.selectedLocale=l(this.localeItemSelector).data("locale"),l("body").on("click",this.localeItemSelector,this.toggleLanguage.bind(this)),i.EventEmitter.on("languageSelected",this.toggleInputs.bind(this)),{localeItemSelector:this.localeItemSelector,localeButtonSelector:this.localeButtonSelector,localeInputSelector:this.localeInputSelector,refreshFormInputs:function(e){n.refreshInputs(e)},getSelectedLocale:function(){return n.selectedLocale}}}return s(e,[{key:"refreshInputs",value:function(e){this.selectedLocale&&i.EventEmitter.emit("languageSelected",{selectedLocale:this.selectedLocale,form:e})}},{key:"toggleLanguage",value:function(e){var t=l(e.target),n=t.closest("form");this.selectedLocale=t.data("locale"),this.refreshInputs(n)}},{key:"toggleInputs",value:function(e){var t=e.form;this.selectedLocale=e.selectedLocale;var n=t.find(this.localeButtonSelector),r=n.data("change-language-url");n.text(this.selectedLocale),t.find(this.localeInputSelector).addClass("d-none"),t.find(this.localeInputSelector+".js-locale-"+this.selectedLocale).removeClass("d-none"),r&&this.saveSelectedLanguage(r,this.selectedLocale)}},{key:"saveSelectedLanguage",value:function(e,t){l.post({url:e,data:{language_iso_code:t}})}}]),e}();t.default=c},function(e,t){
// Copyright Joyent, Inc. and other Node contributors.
//
// Permission is hereby granted, free of charge, to any person obtaining a
// copy of this software and associated documentation files (the
// "Software"), to deal in the Software without restriction, including
// without limitation the rights to use, copy, modify, merge, publish,
// distribute, sublicense, and/or sell copies of the Software, and to permit
// persons to whom the Software is furnished to do so, subject to the
// following conditions:
//
// The above copyright notice and this permission notice shall be included
// in all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN
// NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
// DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
// OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
// USE OR OTHER DEALINGS IN THE SOFTWARE.
function n(){this._events=this._events||{},this._maxListeners=this._maxListeners||void 0}function r(e){return"function"==typeof e}function s(e){return"number"==typeof e}function i(e){return"object"==typeof e&&null!==e}function o(e){return void 0===e}e.exports=n,n.EventEmitter=n,n.prototype._events=void 0,n.prototype._maxListeners=void 0,n.defaultMaxListeners=10,n.prototype.setMaxListeners=function(e){if(!s(e)||e<0||isNaN(e))throw TypeError("n must be a positive number");return this._maxListeners=e,this},n.prototype.emit=function(e){var t,n,s,l,c,a;if(this._events||(this._events={}),"error"===e&&(!this._events.error||i(this._events.error)&&!this._events.error.length)){if((t=arguments[1])instanceof Error)throw t;var u=new Error('Uncaught, unspecified "error" event. ('+t+")");throw u.context=t,u}if(n=this._events[e],o(n))return!1;if(r(n))switch(arguments.length){case 1:n.call(this);break;case 2:n.call(this,arguments[1]);break;case 3:n.call(this,arguments[1],arguments[2]);break;default:l=Array.prototype.slice.call(arguments,1),n.apply(this,l)}else if(i(n))for(l=Array.prototype.slice.call(arguments,1),a=n.slice(),s=a.length,c=0;c<s;c++)a[c].apply(this,l);return!0},n.prototype.addListener=function(e,t){var s;if(!r(t))throw TypeError("listener must be a function");return this._events||(this._events={}),this._events.newListener&&this.emit("newListener",e,r(t.listener)?t.listener:t),this._events[e]?i(this._events[e])?this._events[e].push(t):this._events[e]=[this._events[e],t]:this._events[e]=t,i(this._events[e])&&!this._events[e].warned&&(s=o(this._maxListeners)?n.defaultMaxListeners:this._maxListeners)&&s>0&&this._events[e].length>s&&(this._events[e].warned=!0,console.trace),this},n.prototype.on=n.prototype.addListener,n.prototype.once=function(e,t){function n(){this.removeListener(e,n),s||(s=!0,t.apply(this,arguments))}if(!r(t))throw TypeError("listener must be a function");var s=!1;return n.listener=t,this.on(e,n),this},n.prototype.removeListener=function(e,t){var n,s,o,l;if(!r(t))throw TypeError("listener must be a function");if(!this._events||!this._events[e])return this;if(n=this._events[e],o=n.length,s=-1,n===t||r(n.listener)&&n.listener===t)delete this._events[e],this._events.removeListener&&this.emit("removeListener",e,t);else if(i(n)){for(l=o;l-- >0;)if(n[l]===t||n[l].listener&&n[l].listener===t){s=l;break}if(s<0)return this;1===n.length?(n.length=0,delete this._events[e]):n.splice(s,1),this._events.removeListener&&this.emit("removeListener",e,t)}return this},n.prototype.removeAllListeners=function(e){var t,n;if(!this._events)return this;if(!this._events.removeListener)return 0===arguments.length?this._events={}:this._events[e]&&delete this._events[e],this;if(0===arguments.length){for(t in this._events)"removeListener"!==t&&this.removeAllListeners(t);return this.removeAllListeners("removeListener"),this._events={},this}if(n=this._events[e],r(n))this.removeListener(e,n);else if(n)for(;n.length;)this.removeListener(e,n[n.length-1]);return delete this._events[e],this},n.prototype.listeners=function(e){return this._events&&this._events[e]?r(this._events[e])?[this._events[e]]:this._events[e].slice():[]},n.prototype.listenerCount=function(e){if(this._events){var t=this._events[e];if(r(t))return 1;if(t)return t.length}return 0},n.listenerCount=function(e,t){return e.listenerCount(t)}},function(e,t,n){e.exports=n(0)}]);