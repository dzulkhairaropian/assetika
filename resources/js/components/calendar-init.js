import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";
import listPlugin from "@fullcalendar/list";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";

export function calendarInit() {
  const calendarEl = document.querySelector("#calendar");

  if (calendarEl) {
    const getModalTitleEl = document.querySelector("#event-title");
    const getModalStartDateEl = document.querySelector("#event-start-date");
    const getModalEndDateEl = document.querySelector("#event-end-date");
    const getModalAddBtnEl = document.querySelector(".btn-add-event");
    const getModalUpdateBtnEl = document.querySelector(".btn-update-event");
    const getModalHeaderEl = document.querySelector("#eventModalLabel");

    const openModal = () => {
      const modal = document.getElementById("eventModal");
      if (modal) {
        modal.style.display = "flex";
        document.body.style.overflow = "hidden";
      }
    };

    const closeModal = () => {
      const modal = document.getElementById("eventModal");
      if (modal) {
        modal.style.display = "none";
        document.body.style.overflow = "";
      }
      resetModalFields();
    };

    function resetModalFields() {
      if (getModalTitleEl) getModalTitleEl.value = "";
      if (getModalStartDateEl) getModalStartDateEl.value = "";
      if (getModalEndDateEl) getModalEndDateEl.value = "";
      const checkedRadio = document.querySelector('input[name="event-level"]:checked');
      if (checkedRadio) checkedRadio.checked = false;
    }

    const calendarHeaderToolbar = {
      left: "prev,next addEventButton",
      center: "title",
      right: "dayGridMonth,timeGridWeek,timeGridDay",
    };

    const calendar = new Calendar(calendarEl, {
      plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
      selectable: true,
      editable: true,
      initialView: "dayGridMonth",
      headerToolbar: calendarHeaderToolbar,
      events: "/api/calendar/events",

      select(info) {
        resetModalFields();
        if (getModalHeaderEl) getModalHeaderEl.textContent = "Add Event";
        if (getModalAddBtnEl) getModalAddBtnEl.style.display = "flex";
        if (getModalUpdateBtnEl) getModalUpdateBtnEl.style.display = "none";
        if (getModalStartDateEl) getModalStartDateEl.value = info.startStr.split("T")[0];
        if (getModalEndDateEl) getModalEndDateEl.value = info.endStr ? info.endStr.split("T")[0] : info.startStr.split("T")[0];
        openModal();
      },

      eventClick(info) {
        const eventObj = info.event;
        resetModalFields();

        if (getModalHeaderEl) getModalHeaderEl.textContent = "Edit Event";
        if (getModalTitleEl) getModalTitleEl.value = eventObj.title;
        if (getModalStartDateEl) getModalStartDateEl.value = eventObj.startStr.split("T")[0];
        if (getModalEndDateEl) getModalEndDateEl.value = eventObj.endStr ? eventObj.endStr.split("T")[0] : "";

        const level = eventObj.extendedProps.calendar || "Primary";
        const radio = document.querySelector(`input[value="${level}"]`);
        if (radio) radio.checked = true;

        if (getModalUpdateBtnEl) getModalUpdateBtnEl.dataset.fcEventPublicId = eventObj.id;
        if (getModalAddBtnEl) getModalAddBtnEl.style.display = "none";
        if (getModalUpdateBtnEl) getModalUpdateBtnEl.style.display = "flex";

        openModal();
      },

      eventDrop(info) {
        updateBackendEvent(info.event);
      },
      eventResize(info) {
        updateBackendEvent(info.event);
      },

      customButtons: {
        addEventButton: {
          text: "Add Event +",
          click() {
            resetModalFields();
            if (getModalHeaderEl) getModalHeaderEl.textContent = "Add Event";
            if (getModalAddBtnEl) getModalAddBtnEl.style.display = "flex";
            if (getModalUpdateBtnEl) getModalUpdateBtnEl.style.display = "none";
            const today = new Date().toISOString().split("T")[0];
            if (getModalStartDateEl) getModalStartDateEl.value = today;
            openModal();
          },
        },
      },

      // TailAdmin Official Event Structure Format
      eventContent(eventInfo) {
        const level = String(eventInfo.event.extendedProps.calendar || "Primary").replace(/[^a-zA-Z0-9_-]/g, "");
        const colorClass = `fc-bg-${level.toLowerCase()}`;
        const tempDiv = document.createElement("div");
        tempDiv.textContent = eventInfo.event.title || "";
        const safeTitle = tempDiv.innerHTML;
        return {
          html: `
            <div class="event-fc-color flex items-center fc-event-main ${colorClass}">
              <div class="fc-daygrid-event-dot"></div>
              <div class="fc-event-title">${safeTitle}</div>
            </div>
          `,
        };
      },
    });

    function updateBackendEvent(eventObj) {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
      fetch(`/api/calendar/events/${eventObj.id}`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({
          title: eventObj.title,
          start: eventObj.startStr,
          end: eventObj.endStr,
        }),
      });
    }

    if (getModalAddBtnEl) {
      getModalAddBtnEl.addEventListener("click", () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
        const level = document.querySelector('input[name="event-level"]:checked')?.value || "Primary";

        fetch("/api/calendar/events", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
          },
          body: JSON.stringify({
            title: getModalTitleEl.value,
            start: getModalStartDateEl.value,
            end: getModalEndDateEl.value,
            calendar: level,
          }),
        }).then(() => {
          calendar.refetchEvents();
          closeModal();
        });
      });
    }

    if (getModalUpdateBtnEl) {
      getModalUpdateBtnEl.addEventListener("click", () => {
        const id = getModalUpdateBtnEl.dataset.fcEventPublicId;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

        fetch(`/api/calendar/events/${id}`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
          },
          body: JSON.stringify({
            title: getModalTitleEl.value,
            start: getModalStartDateEl.value,
            end: getModalEndDateEl.value,
          }),
        }).then(() => {
          calendar.refetchEvents();
          closeModal();
        });
      });
    }

    calendar.render();

    document.querySelectorAll(".modal-close-btn").forEach((btn) => {
      btn.addEventListener("click", closeModal);
    });

    window.addEventListener("click", (event) => {
      const modal = document.getElementById("eventModal");
      if (event.target === modal) closeModal();
    });
  }
}

export default calendarInit;
