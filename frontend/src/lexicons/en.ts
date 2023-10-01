export default {
  project: 'Orbita',
  actions: {
    save: 'Save',
    submit: 'Submit',
    cancel: 'Cancel',
    close: 'Close',
    delete: 'Delete',
    create: 'Create',
    edit: 'Edit',
    add: 'Add',
  },
  security: {
    login: 'Login',
    logout: 'Logout',
    register: 'Register',
    reset: 'Forgot password',
    reset_desc: `You can request a one-time link to log in to your account. After logging in, you will be able to change your old password.`,
  },
  models: {
    user: {
      title_one: 'User',
      title_many: 'Users',
      id: 'Id',
      username: 'Login',
      password: 'Password',
      fullname: 'Name',
      email: 'E-mail',
      phone: 'Phone',
      active: 'Activated',
      blocked: 'Blocked',
      role: 'Role',
    },
    user_role: {
      title_one: 'User Role',
      title_many: 'Users Roles',
      id: 'Id',
      title: 'Title',
      scope: 'Scope',
      users: 'Users',
    },
  },
  components: {
    table: {
      no_data: 'Nothing to display',
      no_results: 'Nothing found',
      records: 'No records | 1 record | {total} records',
      query: 'Search...',
      delete: {
        title: 'Confirmation required',
        confirm: 'Are you sure you want to delete this entry?',
      },
    },
  },
  pages: {
    index: 'Main',
    admin: {
      title: 'Admin',
      users: 'Users',
      user_roles: 'Roles',
    },
    user: {
      title: 'Account',
      profile: 'Profile',
    },
  },
  success: {
    profile: 'Profile saved successfully',
  },
  errors: {
    user: {
      no_username: 'Username is required',
      no_fullname: 'Fullname is required',
      no_email: 'Email is required',
      username_exists: 'This username is already exists',
      email_exists: 'This email is already exists',
      inactive: 'This account is not active',
      blocked: 'This account is blocked',
    },
    login: {
      wrong: 'Wrong username or password',
    },
    activate: {
      no_user: 'The user does not exist or did not reset the password',
      ttl: 'The link has expired',
      wrong: 'Incorrect temporary password',
    },
  },
}
