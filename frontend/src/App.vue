<template>
  <div>
    <div id="app">
      <van-nav-bar :title="navTitle" :right-text="navButton" @click-right="onClickRight()" />
      <van-dialog v-model="showLogin" :closeOnClickOverlay="true" :showConfirmButton="false" class="auth-dialog" width="600px" >
        <div class="info-div">
          <van-cell-group>
            <van-field v-model="username" required clearable label="用户名" placeholder="请输入用户名" :error-message="errorMessage.account" @input="accountValidation()" />
            <van-field v-model="password" type="password" label="密码" placeholder="请输入密码" required :error-message="errorMessage.password" @input="accountValidation()" />
            <div class="auth-btn">
              <van-button type="primary" @click="doLogin()" >登录</van-button>
              <van-button type="default" hairline @click="doRegister()" >注册</van-button>
            </div>
          </van-cell-group>
        </div>
      </van-dialog>
      <div class="todo-div">
        <van-icon name="arrow-down" v-if="activeNum + completedNum > 0" class="select-icon" @click="changeAllStatus()" />
        <van-icon v-else name="plus" class="select-icon" />
        <van-field v-model="newTodo" @keyup.enter="submitTodo" class="input-box" ></van-field>
        <van-list class="todo-list">
          <van-cell v-for="(item, index) in dataList" v-bind:key="item.id" >
            <todo :item="item" :index="index" @handleDel="handleDel" @changeDescription="changeDescription" @handleStatus="handleStatus" ></todo>
          </van-cell>
        </van-list>
        <div v-if="activeNum + completedNum > 0" class="btn-list" >
          <label v-if="activeNum == 1">1 item left</label>
          <label v-else>{{ activeNum }} items left</label>
          <van-button text="all" type="default" @click="checkToAll()" ></van-button>
          <van-button text="active" type="default" @click="checkToActive()" ></van-button>
          <van-button text="completed" type="default" @click="checkToCompleted()" ></van-button>
          <a @click="clearCompleted()">clear completed</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Qs from "qs";
import Todo from "./components/Todo.vue";
import { List, Cell, CellGroup, Field, Dialog, Button, Icon, Overlay, NavBar } from "vant";
export default {
  name: "APP",
  components: {
    Todo,
    [List.name]: List,
    [Cell.name]: Cell,
    [Field.name]: Field,
    [Button.name]: Button,
    [Icon.name]: Icon,
    [Overlay.name]: Overlay,
    [NavBar.name]: NavBar,
    [CellGroup.name]: CellGroup,
    [Dialog.Component.name]: Dialog.Component
  },
  data() {
    return {
      username: "",
      password: "",
      newTodo: "",
      dataList: [],
      allData: [],
      activeList: [],
      completedList: [],
      activeNum: 0,
      completedNum: 0,
      showLogin: false,
      navTitle: "",
      isLogin: false,
      navButton: "登录/注册",
      errorMessage: { account: "", password: "" }
    };
  },
  mounted() {
    this.navTitle = this.getCookie("username");
    if (this.navTitle) {
      this.navButton = "注销";
      this.isLogin = true;
    }
    this.checkToAll();
  },
  methods: {
    onClickRight() {
      if (this.isLogin) {
        return this.logout();
      }
      this.showLogin = true;
    },
    accountValidation() {
      var validation = true;
      if (!/^[a-zA-Z0-9_-]{4,16}$/.test(this.username)) {
        this.errorMessage.account =
          "请填写 4 到 16 位（字母，数字，下划线，减号）用户名";
        validation = false;
      } else {
        this.errorMessage.account = "";
      }
      if (!/^[a-zA-Z0-9_!.@#$%^&*?]{4,16}$/.test(this.password)) {
        this.errorMessage.password =
          "请填写不少于 4 位（字母，数字，常见特殊字符）密码";
        validation = false;
      } else {
        this.errorMessage.password = "";
      }
      return validation;
    },
    doLogin() {
      if (!this.accountValidation()) {
        return;
      }
      var data = Qs.stringify({
        username: this.username,
        password: this.password
      });
      this.$http.post("/login", data).then(res => {
        if (res.data) {
          var data = res.data;
          var expire = data.expire;
          delete data.expire;
          for (var key in data) {
            this.setCookie(key, data[key], expire);
          }
          this.setCookie("username", this.username, expire);
          this.showLogin = false;
          this.navTitle = this.username;
          this.navButton = "注销";
          this.isLogin = true;
          this.checkToAll();
        } else {
          this.errorMessage.username = res.data.username;
          this.errorMessage.password = res.data.password;
        }
      });
    },
    doRegister() {
      if (!this.accountValidation()) {
        return;
      }
      this.$http.post("/register", Qs.stringify({ username: this.username, password: this.password })).then(res => {
        if (res.status == 200) {
          this.showLogin = false;
        } else {
          this.errorMessage.username = res.data.username;
          this.errorMessage.password = res.data.password;
        }
      });
    },
    logout() {
      this.$http.post("/logout").then(res => {
        if (res.status == 200) {
          var data = res.data;
          var expire = data.expire;
          delete data.expire;
          for (var key in data) {
            this.setCookie(key, data[key], expire);
          }
          this.setCookie("username", "", -1);
          this.navTitle = "";
          this.navButton = "登录/注册";
          this.isLogin = false;
          this.dataList = [];
          this.allData = [];
          this.activeList = [];
          this.completedList = [];
          this.activeNum = 0;
          this.completedNum = 0;
        } else {
          this.setCookie("username", "", -1);
          this.setCookie("accessToken", "", -1);
          this.setCookie("userId", "", -1);
          alert(res.data); // 登录失败，显示提示语
        }

      });
    },
    submitTodo() {
      if (!this.newTodo) {
        return;
      }
      var todoItem = { description: this.newTodo };
      this.$http.post("/todos", Qs.stringify(todoItem)).then(res => {
        if (res.status == 200) {
          var data = {
            _id: res.data._id,
            user_id: res.data.user_id,
            description: res.data.description,
            status: res.data.status
          };
          this.allData.unshift(data);
          this.activeList.unshift(data);
          this.newTodo = "";
          this.activeNum += 1;
        } else {
          if (res.status == 401) {
            this.showLogin = true
          }
        }
      });
    },
    handleDel(index) {
      this.$http.delete("/todos/" + this.dataList[index]._id).then(res => {
        if (res.status == 200) {
          if (this.dataList[index].status == true) {
            this.activeNum -= 1;
          } else {
            this.completedNum -= 1;
          }
          this.dataList.splice(index, 1);
        } else {
          alert("error");
        }
      });
    },
    handleStatus(status, index) {
      this.$http.patch("/todos/" + this.dataList[index]._id + "/status").then(res => {
        if (res.status == 200) {
          this.dataList[index].status = res.data.status;
          if (status) {
            this.activeList.unshift(this.dataList[index]);
            this.activeNum += 1;
            this.completedNum -= 1;
          } else {
            this.completedList.unshift(this.dataList[index]);
            this.activeNum -= 1;
            this.completedNum += 1;
          }
          if (this.dataList === this.allData) {
            return;
          }
          this.dataList.splice(index, 1);
        } else {
          alert("error");
        }
      });
    },
    checkToAll() {
      this.$http.get("/todos").then(res => {
        if (res.data) {
          this.allData.splice(0, this.allData.length);
          this.activeList.splice(0, this.activeList.length);
          this.completedList.splice(0, this.completedList.length);
          var data = res.data;
          this.activeNum = 0;
          this.completedNum = 0;
          for (var i = 0; i < data.length; i++) {
            var todo = {
              _id: data[i]._id,
              user_id: data[i].user_id,
              description: data[i].description,
              status: data[i].status
            };
            this.allData.unshift(todo);
            if (data[i].status == true) {
              this.activeList.unshift(todo);
              this.activeNum += 1;
            } else {
              this.completedList.unshift(todo);
              this.completedNum += 1;
            }
          }
          this.dataList = this.allData;
        } else {
          alert("error");
        }
      });
    },
    checkToActive() {
      this.$http.get("/todos/starts").then(res => {
        if (res.status == 200) {
          this.activeList.splice(0, this.activeList.length);
          var data = res.data;
          for (var i = 0; i < data.length; i++) {
            this.activeList.unshift({
              _id: data[i]._id,
              user_id: data[i].user_id,
              description: data[i].description,
              status: data[i].status
            });
          }
          this.activeNum = this.activeList.length;
          this.dataList = this.activeList;
        } else {
          alert("error");
        }
      });
    },
    checkToCompleted() {
      this.$http.get("/todos/ends").then(res => {
        if (res.status == 200) {
          this.completedList = [];
          var data = res.data;
          for (var i = 0; i < data.length; i++) {
            this.completedList.unshift({
              _id: data[i]._id,
              user_id: data[i].user_id,
              description: data[i].description,
              status: data[i].status
            });
          }
          this.dataList = this.completedList;
        } else {
          alert('error');
        }
      });
    },
    changeDescription(obj) {
      this.dataList[obj.index].description = obj.value;
      this.$http.patch("/todos/" + this.dataList[obj.index]._id + "/description",Qs.stringify({ description: obj.value })).then(res => {
        if (res.status == 200) {
          this.dataList[obj.index].description = res.data.description;
        } else {
          alert("error");
        }
      });
    },
    changeAllStatus() {
      var status;
      if (this.activeList.length > 0) {
        status = false;
      } else {
        status = true;
      }
      this.$http.patch("/todos/status", Qs.stringify({ status: status })).then(res => {
        if (res.status == 200) {
          this.completedList.splice(0, this.completedList.length);
          this.activeList.splice(0, this.activeList.length);
          this.allData.splice(0, this.allData.length);
          var data = res.data;
          this.activeNum = 0;
          for (var i = 0; i < data.length; i++) {
            var todo = {
              _id: data[i]._id,
              user_id: data[i].user_id,
              description: data[i].description,
              status: data[i].status
            };
            this.allData.unshift(todo);
            if (status) {
              this.activeList.unshift(todo);
              this.activeNum += 1;
            } else {
              this.completedList.unshift(todo);
              this.completedNum += 1;
            }
          }
        } else {
          alert("error");
        }
      });
    },
    clearCompleted() {
      this.$http.delete("/todos/ends", {}).then(res => {
        if (res.status == 200) {
          this.completedList.splice(0, this.completedList.length);
          for (var i = 0; i < this.dataList.length; ) {
            if (!this.dataList[i].status) {
              this.dataList.splice(i, 1);
              continue;
            }
            i++;
          }
          this.completedNum = 0;
        } else {
          alert("error");
        }
      });
    },
    setCookie(cname, cvalue, exseconds) {
      var d = new Date();
      d.setTime(d.getTime() + exseconds * 1000);
      var expires = "expires=" + d.toUTCString();
      document.cookie = cname + "=" + cvalue + "; " + expires;
    },
    getCookie(name) {
      var reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
      var arr = document.cookie.match(reg);
      if (arr) {
        return arr[2];
      } else {
        return null;
      }
    },
  }
};
</script>

<style scoped>
.info-div {
  position: absolute;
  width: 400px;
  height: 100px;
  left: 50%;
  top: 40%;
  transform: translateX(-50%) translateY(-50%);
}
.todo-div {
  position: relative;
  width: 50%;
  margin: 5px;
  top: 100px;
  background: #fff;
  left: 25%;
  transform: translateX(-50%), translateY(-50%);
}
.select-icon,
.input-box {
  display: table-cell;
}
.input-box {
  padding-left: 5px;
  font-size: 20px;
}
.select-icon {
  position: relative;
  top: 7px;
  left: 5px;
  margin-right: 5px;
  padding-right: 10px;
  font-size: 25px;
}
.todo-div .todo-list {
  top: 50px;
  max-height: 400px;
  background: #ccc;
  overflow: auto;
}
.btn-list {
  padding: 10px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.btn-list button {
  border: none;
  font-size: inherit;
}
.btn-list button:hover {
  border: 1px solid #a3a3a3;
}
.btn-list a:hover {
  text-decoration: underline;
  cursor: pointer;
}
.auth-dialog {
  height: 500px;
  position: relative;
}
.auth-btn {
  width: 100%;
  display: flex;
  justify-content: space-evenly;
}
</style>
