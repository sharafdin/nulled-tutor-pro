/**
 * Facebook authentication
 * 
 * @since 2.1.9
 */
const {__} = wp.i18n;
const defaultErrMsg = __( 'Something went wrong, please try again', 'tutor-pro' );
const {facebook_app_id, current_user_id, logout_url} = tutorProSocialLogin;

function checkLoginState() {
	FB.getLoginStatus(function(response) {
		statusChangeCallback(response);
	});
}

(function(d, s, id){
    var fbScript, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {return;}
    fbScript = d.createElement(s); fbScript.id = id;
    fbScript.src = "//connect.facebook.net/en_US/all.js";
    fbScript.async = true; // add async attribute
    fjs.parentNode.insertBefore(fbScript, fjs);
  }(document, 'script', 'facebook-jssdk'));

window.fbAsyncInit = function() {
    FB.init({
        appId      : facebook_app_id,
        cookie     : true,
        xfbml      : true,
        version    : 'v16.0'
    });

//     FB.getLoginStatus(function(response) {
//         statusChangeCallback(response);
//     });
};

function statusChangeCallback(response) {

    if (response.status === 'connected') {
        FB.api('/me', { locale: 'en_US', fields: 'name,email,first_name,last_name,picture' }, async (payload) => {

            const tutorAction = document.querySelector('form input[name=tutor_action]');
            const redirectTo = document.querySelector('form input[name=redirect_to]');

            let authAttempt = 'tutor_user_login';
            if ( tutorAction ) {
                authAttempt = tutorAction.value;
            }

            const prepareFormData = [
                {action: 'tutor_pro_social_authentication'},
                {token: response.authResponse.accessToken},
                {auth: 'facebook'},

                {first_name: payload.first_name},
                {last_name: payload.last_name},
                {user_login: payload.name.replace( ' ', '_')},
                {email: payload.email},
                {auth_user_id: payload.id},
                {profile_url: payload.picture && payload.picture.data ? payload.picture.data.url : ''},
                {attempt: authAttempt}
            ];
            const formData = tutorFormData(prepareFormData);

            try {
                tutor_toast(
                    __("Authentication Processed", "tutor-pro"),
                    __('Please wait...', 'tutor-pro'),
                    "success"
                );
                const post = await ajaxHandler(formData);
                const res = await post.json();
            
                const {success ,data} = res;
        
                if (success) {
                    tutor_toast(__("Authentication success", "tutor-pro"), data, "success");
                    if (redirectTo) {
                        window.location.href = `${redirectTo.value}"?nocache=${(new Date()).getTime()}`;
                    } else {
                        window.location.href = `${_tutorobject.tutor_frontend_dashboard_url}"?nocache=${(new Date()).getTime()}`;
                    }
                } else {
                    if (Array.isArray(data)) {
                        let error = data[0];
                        if (error && error.code === 'tutor_login_limit') {
                            const loginWrapper = document.querySelector('.tutor-login-form-wrapper');
                            loginWrapper.insertAdjacentHTML(
                                'afterbegin',
                                `<div class="tutor-alert tutor-warning tutor-mb-12" style="display:block;">${error.message}</div>`
                            );
                            return;
                        }
                    }
                    tutor_toast(__("Authentication failed", "tutor-pro"), data, "error");
                }
            } catch(err) {
                tutor_toast(__("Authentication failed", "tutor-pro"), defaultErrMsg, "error");
            }
    
        })
    }
}

function tutorFormData(data = []) {
    const formData = new FormData();
    data.forEach((item) => {
        for (const [key, value] of Object.entries(item)) {
            formData.set(key, value)
        }
    });
    formData.set(window.tutor_get_nonce_data(true).key, window.tutor_get_nonce_data(true).value);
    return formData;
}


async function ajaxHandler(formData) {
    try {
      const post = await fetch(window._tutorobject.ajaxurl, {
        method: "POST",
        body: formData,
      });
      return post;
    } catch (error) {
      tutor_toast(__("Operation failed", "tutor"), error, "error");
    }
}
  
// window.addEventListener('DOMContentLoaded', function() {
//     const logout = document.querySelector('li.tutor-dashboard-menu-item a[data-no-instant]');
//     const tutorStarterSubmenu = document.querySelectorAll(".tutor-header-submenu");
	
	
//     if (logout) {
//         logout.onclick = (e) => {
//             e.preventDefault();
    
//             // Logout from FB
//             tutor_social_logout();
//         }
//     }

//     tutorStarterSubmenu.forEach(elem => {
//         elem.addEventListener('click', function(e) {
//             let tag = e.target.tagName;
//             if ( tag === 'A' && e.target.hasAttribute('data-no-instant') ) {
//                 e.preventDefault();

//                 // Logout from FB
//                 tutor_social_logout();
//             }
//         })
//     })

//     function tutor_social_logout() {   
// 	    try {
// 		    FB.logout(function(response) {
                    
//             });
// 		} catch (err) {
// 			//console.log(err)
// 		} finally {
// 			window.location.href = `${logout_url}"?nocache=${(new Date()).getTime()}`;
// 		}
        
//     }
// });