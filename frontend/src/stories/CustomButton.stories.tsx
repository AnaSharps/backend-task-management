/* eslint-disable import/no-extraneous-dependencies */
/* eslint-disable react/jsx-props-no-spreading */
import React from "react";
import { Story, Meta } from "@storybook/react";
import { SearchOutlined } from "@ant-design/icons";
import "../App.less";
import { CustomButton, ButtonProps } from "../components/Button";

export default {
	title: "Shared/CustomButton",
	component: CustomButton,
} as Meta;

const Template: Story<ButtonProps> = (args) => <CustomButton {...args} />;

export const Primary = Template.bind({});
Primary.args = {
	isSecondary: false,
	text: "LOGIN",
	size: "large",
	block: true,
};

export const Secondary = Template.bind({});
Secondary.args = {
	isSecondary: true,
	text: "Button",
	size: "large",
};

export const RoundButton = Template.bind({});
RoundButton.args = {
	prefixIcon: <SearchOutlined size={24} />,
	size: "large",
	round: true,
	text: " ",
	style: {
		height: "60px",
		width: "80px",
	},
};
