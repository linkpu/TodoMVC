import axios from "axios"

// axios 配置
axios.defaults.timeout = 5000;
axios.defaults.baseURL = "http://localhost:8080";
axios.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";

function getCookie(name) {
  var reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
  var arr = document.cookie.match(reg);
  if (arr) {
    return arr[2];
  } else {
    return null;
  }
}

axios.interceptors.request.use(
  request => {
    var accessToken = getCookie("accessToken");
    var userId = getCookie("userId")
    if (accessToken && userId) {
      request.url += "?accessToken=" + accessToken + "&userId=" + userId;
    }
    return request;
  },
  err => {
    return Promise.reject(err);
  }
);

axios.interceptors.response.use(
  response => {
    return response;
  },
  error => {
    if (error.response) {
      switch (error.response.status) {
        case 401:
      }
    }
    return Promise.reject(error)
});
export default axios;
