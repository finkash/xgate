import axios from 'axios';
window.axios = axios;

/*
* Set the default header for AJAX requests to indicate that they are XMLHttpRequests, 
* which can be used by the server to handle the request appropriately. 
* This is particularly useful for distinguishing between regular page requests and AJAX requests, 
* allowing the server to return the correct response format (e.g., JSON for AJAX requests).
* This line is important for security and functionality, as it helps prevent certain types of attacks (like CSRF) 
* and ensures that the server can properly identify and respond to AJAX requests.
*/
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
