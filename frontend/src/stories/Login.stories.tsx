/* eslint-disable react/jsx-props-no-spreading */
/* eslint-disable import/no-extraneous-dependencies */
import React from "react";
import "../App.less";
import { Story, Meta } from "@storybook/react";

import { Login, LoginProps } from "../screens/Login";

export default {
	title: "shared/SignInForm",
	component: Login,
} as Meta;

const Template: Story<LoginProps> = (args) => <Login {...args} />;

export const Primary = Template.bind({});
