(function(Drupal) {
  // if window.kinetic.breakpoints is undefined, create it
  if (window.kinetic === undefined) {
    window.kinetic = {};
  }

  if (window.kinetic.breakpoints === undefined) {
    window.kinetic.breakpoints = {
      all: {},
      current: {},
      init() {
        this.collectBreakpoints();

        if (Object.keys(this.all).length > 0) {
          // Reverse the order of the breakpoints so that the largest breakpoint is first.
          Object.keys(this.all).reverse().forEach((key) => {
            const bp = this.all[key];
            const nextBp = this.getNextBreakpoint(bp.name);
            const mql = window.matchMedia(this.all[key].mediaQuery + (nextBp ? ` and (max-width: ${nextBp.value - 1}px)` : ''));
            mql.breakpointName = bp.name;
            // Find the initial breakpoint.
            if (mql.matches && Object.keys(this.current).length === 0) {
              this.emitChangeEvent(bp);
            }
            // Add a change event to all the breakpoints.
            mql.onchange = (e) => {
              if (e.matches) {
                const bp = this.all[e.target.breakpointName];
                this.emitChangeEvent(bp);
              }
            };
          });
        }
      },
      /**
       * Returns the next breakpoint in the list of breakpoints given a breakpoint name.
       * @param breakpointName
       *   The name of the breakpoint to find the next breakpoint for.
       * @returns {object|null}
       */
      getNextBreakpoint(breakpointName) {
        const sortedBreakpoints = Object.values(this.all).sort((a, b) => a.value - b.value);
        const currentBreakpointIndex = sortedBreakpoints.findIndex((bp) => bp.name === breakpointName);

        if (currentBreakpointIndex !== sortedBreakpoints.length - 1) {
          return sortedBreakpoints[currentBreakpointIndex + 1];
        } else {
          return null; // No next breakpoint exists
        }
      },
      /**
       * Collects breakpoints from CSS variables and returns an object with the breakpoint names as keys.
       * See source/01-base/global/scss/general/_breakpoints.scss
       */
      collectBreakpoints() {
        const tempEl = document.createElement('div');
        tempEl.classList.add('bs-breakpoints');
        document.body.appendChild(tempEl);
        // bs-breakponts class contains a css var of all the brekapoints configured in the theme.
        const style = window.getComputedStyle(tempEl).getPropertyValue('--breakpoints');
        // Remove the surrounding quotes and trailing comma from the string.
        const breakPoints = style.substring(1, style.length - 2);
        const breakpointArray = breakPoints.split(',');
        breakpointArray.forEach((breakpoint) => {
          const [name, value] = breakpoint.split(':');
          this.all[name] = {
            name,
            value: parseInt(value, 10),
            mediaQuery: `(min-width: ${value})`,
          };
        });
        // Remove the temp element from the DOM.
        tempEl.remove();
      },
      /**
       * Emit a breakpointChange event when the viewport changes.
       */
      emitChangeEvent(bp) {
        // Emit event with the new breakpoint
        if (bp !== this.current) {
          this.current = bp;
          document.dispatchEvent(new CustomEvent('breakpointChange', { detail: bp }));
        }
      },
      /**
       * Returns true if the current viewport is at least as wide as the given breakpoint.
       * @param breakpointName
       * @returns {boolean|boolean}
       */
      mediaBreakpointUp(breakpointName) {
        const breakpoint = this.all[breakpointName];
        return breakpoint ? matchMedia(breakpoint.mediaQuery).matches : false;
      },
      /**
       * Returns true if the current viewport is at most as wide as the breakpoint above the given breakpoint.
       * @param breakpointName
       * @returns {boolean|boolean}
       */
      mediaBreakpointDown(breakpointName) {
        const breakpoint = this.all[breakpointName];
        return breakpoint ? matchMedia(`(max-width: ${breakpoint.value - 1}px)`).matches : false;
      },
      /**
       * Returns true if the current viewport is at least as wide as the given breakpoint and at most as wide as the breakpoint above the given breakpoint.
       * @param breakpointName
       * @returns {boolean}
       */
      mediaBreakpointOnly(breakpointName) {
        const breakpoint = this.all[breakpointName];
        if (!breakpoint) return false;

        // get the next breakpoint
        const nextBreakpoint = this.getNextBreakpoint(breakpointName);
        if (nextBreakpoint) {
          return (
            this.mediaBreakpointDown(breakpointName) &&
            this.mediaBreakpointUp(breakpointName)
          );
        } else {
          return true;
        }
      },
    }

    window.kinetic.breakpoints.init();

    // Example usage of the breakpointChange event:
    // document.addEventListener('breakpointChange', (event) => {
    //   console.log(event.detail);
    // });
  }

})(Drupal);
