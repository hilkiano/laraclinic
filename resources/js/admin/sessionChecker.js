import { jwt } from "./getToken";

const guardedPath = ["/"];

/**
 * Check if session still active and get the user information
 */
const sessionCheck = async () => {
  if (jwt) {
    const user = await fetch("api/v1/me");
    const resUser = await user.json();
    if (!resUser.status) {
      window.location = "/login";
    }
  } else {
    window.location = "/login";
  }
};

(function () {
  if (guardedPath.includes(window.location.pathname)) {
    sessionCheck();
  }
})();
