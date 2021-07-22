import { configureStore, ThunkAction, Action } from "@reduxjs/toolkit";
import counterReducer from "../features/counter/counterSlice";
import signupReducer from "../features/signupForm";
import verifyEmailReducer from "../features/verifyEmailSent";

export const store = configureStore({
	reducer: {
		counter: counterReducer,
		signup: signupReducer,
		verifyEmail: verifyEmailReducer,
	},
});

export type AppDispatch = typeof store.dispatch;
export type RootState = ReturnType<typeof store.getState>;
export type AppThunk<ReturnType = void> = ThunkAction<
	ReturnType,
	RootState,
	unknown,
	Action<string>
>;
