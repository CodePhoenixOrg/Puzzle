var PZ_VERTICAL_SCROLL = 1;
var PZ_HORIZONTAL_SCROLL = 2;
var PZ_MOUSE_X;
var PZ_MOUSE_Y;
var PZ_CURSOR;

function pz_cursor(thisCursor, f_height, f_width) {
	this.path = pz_getPath(thisCursor);
	this.cursor = eval(this.path);
	this.name = thisCursor;
	this.top = 0;
	this.left = 0;
	this.height = f_height;
	this.width = f_width;
	this.scroller = null;

	if (this.cursor)
		this.style = this.cursor.style;
	else
		alert("L'objet " + this.name + " n'existe pas!");
}

function pz_init_cursorX() {
	this.step = 0;
	this.cursor.style.left = this.scroller.offsetLeft + 13;
	this.cursor.style.top = this.scroller.offsetTop;
	this.direction = PZ_HORIZONTAL_SCROLL;
}

function pz_init_cursorY() {
	this.step = 0;
	this.cursor.style.left = this.scroller.offsetLeft;
	this.cursor.style.top = this.scroller.offsetTop + 13;
	this.direction = PZ_VERTICAL_SCROLL;
}

function pz_move(o, x, y) {
	o.pixelLeft = x;
	o.pixelTop = y;
	if (PZ_CURSOR.direction == PZ_HORIZONTAL_SCROLL)
		PZ_SCROLLBOX.scrollXByPercent();
	else if (PZ_CURSOR.direction == PZ_VERTICAL_SCROLL)
		PZ_SCROLLBOX.scrollYByPercent();
}

function pz_moveByPercent() {
	var cursorLeft, cursorTop;

	if (PZ_CURSOR.direction == PZ_HORIZONTAL_SCROLL) {

		cursorTop = PZ_CURSOR.scroller.offsetTop;
		cursorLeft = PZ_CURSOR.scroller.offsetLeft + 13 + PZ_SCROLLBOX.cursorXPos * (PZ_CURSOR.scroller.offsetWidth - 26 - PZ_CURSOR.width);

		PZ_CURSOR.style.top = cursorTop;
		PZ_CURSOR.style.left = cursorLeft;

		PZ_SCROLLBOX.scrollXByPercent();
	} else if (PZ_CURSOR.direction == PZ_VERTICAL_SCROLL) {

		cursorTop = PZ_CURSOR.scroller.offsetTop + 13 + PZ_SCROLLBOX.cursorYPos * (PZ_CURSOR.scroller.offsetHeight - 26 - PZ_CURSOR.height);
		cursorLeft = PZ_CURSOR.scroller.offsetLeft;

		PZ_CURSOR.style.top = cursorTop;
		PZ_CURSOR.style.left = cursorLeft;

		PZ_SCROLLBOX.scrollYByPercent();
	}
}

function pz_mouseMove() {
	var cursorLeft, cursorTop;
	var higherLimit, lowerLimit;

	if (PZ_CURSOR.style) {
		if (PZ_CURSOR.direction == PZ_HORIZONTAL_SCROLL) {

			lowerLimit = PZ_CURSOR.scroller.offsetLeft + 13;
			higherLimit = PZ_CURSOR.scroller.offsetLeft + PZ_CURSOR.scroller.offsetWidth - 13 - PZ_CURSOR.width;

			cursorTop = PZ_CURSOR.scroller.offsetTop;
			cursorLeft = window.event.clientX - PZ_MOUSE_X + document.body.scrollLeft - 2;

			if (cursorLeft < lowerLimit) {
				cursorLeft = lowerLimit;
			} else if (cursorLeft > higherLimit) {
				cursorLeft = higherLimit;
			}

			PZ_SCROLLBOX.cursorXPos = (cursorLeft - lowerLimit) / (PZ_CURSOR.scroller.offsetWidth - 26 - PZ_CURSOR.width);

			pz_move(PZ_CURSOR.style, cursorLeft, cursorTop);

		} else if (PZ_CURSOR.direction == PZ_VERTICAL_SCROLL) {

			lowerLimit = PZ_CURSOR.scroller.offsetTop + 13;
			higherLimit = PZ_CURSOR.scroller.offsetTop + PZ_CURSOR.scroller.offsetHeight - 13 - PZ_CURSOR.height;

			cursorTop = window.event.clientY - PZ_MOUSE_Y + document.body.scrollTop - 1;
			cursorLeft = PZ_CURSOR.scroller.offsetLeft;

			if (cursorTop < lowerLimit) {
				cursorTop = lowerLimit;
			} else if (cursorTop > higherLimit) {
				cursorTop = higherLimit;
			}

			PZ_SCROLLBOX.cursorYPos = (cursorTop - lowerLimit) / (PZ_CURSOR.scroller.offsetHeight - 26 - PZ_CURSOR.height);

			pz_move(PZ_CURSOR.style, cursorLeft, cursorTop);
		}
		return false;
	}
}

function pz_mouseDown() {
	if (PZ_CURSOR.style) {
		PZ_MOUSE_X = window.event.offsetX;
		PZ_MOUSE_Y = window.event.offsetY;
	}
	return false;
}

function pz_mouseUp() {
	pz_cancelEvents();
}

function pz_changeEvents() {
	document.onmousedown = this.mouseDown;
	document.onmousemove = this.mouseMove;
	document.onmouseup = this.mouseUp;
	window.status = '';
	return true;
}

function pz_cancelEvents() {
	document.onmousedown = function () { return false };
	document.onmousemove = function () { return false };
	document.onmouseup = function () { return false };
	window.status = '';
	return true;
}

pz_cursor.prototype.init_cursorX = pz_init_cursorX;
pz_cursor.prototype.init_cursorY = pz_init_cursorY;
pz_cursor.prototype.move = pz_move;
pz_cursor.prototype.moveByPercent = pz_moveByPercent;
pz_cursor.prototype.mouseMove = pz_mouseMove;
pz_cursor.prototype.mouseDown = pz_mouseDown;
pz_cursor.prototype.mouseUp = pz_mouseUp;
pz_cursor.prototype.changeEvents = pz_changeEvents;
pz_cursor.prototype.cancelEvents = pz_cancelEvents;
